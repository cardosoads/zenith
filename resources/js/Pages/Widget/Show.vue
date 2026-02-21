<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/UI/Modal.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    provider: Object,
    agenda: Object,
    services: Array,
});

const step = ref(1);
const selectedServiceId = ref(props.services?.[0]?.id ?? null);
const date = ref(new Date().toISOString().slice(0, 10));
const selectedSlot = ref(null);
const slots = ref([]);
const monthCursor = ref(new Date(`${date.value}T00:00:00`));
const availabilityByDate = ref({});
const loadingMonth = ref(false);
const availabilityRequestId = ref(0);
const slotsModalOpen = ref(false);
const selectedFiles = ref([]);

const booking = ref({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    customer_notes: '',
    extra_fields: (props.agenda?.customer_extra_fields ?? []).slice(0, 3),
});
const confirmation = ref(null);

const dayLabels = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
const todayIso = new Date().toISOString().slice(0, 10);

const selectedService = computed(() => props.services.find((service) => service.id === selectedServiceId.value));
const isPaid = computed(() => (selectedService.value?.price_cents ?? 0) > 0);
const monthTitle = computed(() => {
    return monthCursor.value.toLocaleDateString('pt-BR', {
        month: 'long',
        year: 'numeric',
    });
});

const selectedDateLabel = computed(() => {
    return new Date(`${date.value}T00:00:00`).toLocaleDateString('pt-BR');
});

const monthDays = computed(() => {
    const baseDate = new Date(monthCursor.value.getFullYear(), monthCursor.value.getMonth(), 1);
    const firstWeekday = baseDate.getDay();
    const daysInMonth = new Date(baseDate.getFullYear(), baseDate.getMonth() + 1, 0).getDate();

    const cells = [];

    for (let i = 0; i < firstWeekday; i += 1) {
        cells.push(null);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
        const fullDate = new Date(baseDate.getFullYear(), baseDate.getMonth(), day);
        const isoDate = fullDate.toISOString().slice(0, 10);
        cells.push({
            day,
            isoDate,
        });
    }

    while (cells.length % 7 !== 0) {
        cells.push(null);
    }

    return cells;
});

const queryParams = computed(() => {
    const params = new URLSearchParams(window.location.search);

    return {
        theme: params.get('theme'),
        accent: params.get('accent'),
        density: params.get('density'),
        preset: params.get('preset'),
    };
});

const presets = {
    clean: {},
    contrast: {
        '--za-border': 'color-mix(in srgb, var(--twilight-indigo) 45%, white 55%)',
        '--za-border-strong': 'color-mix(in srgb, var(--twilight-indigo) 70%, white 30%)',
    },
    soft: {
        '--za-surface-2': 'color-mix(in srgb, var(--sky-blue) 14%, var(--za-surface) 86%)',
    },
    editorial: {
        '--za-font-display': "'Space Grotesk', 'Inter', sans-serif",
        '--za-border-strong': 'color-mix(in srgb, var(--honey-bronze) 55%, black 45%)',
    },
};

const applyWidgetTheme = () => {
    const html = document.documentElement;
    const selectedTheme = queryParams.value.theme || props.agenda.theme || 'auto';
    const selectedAccent = queryParams.value.accent || props.agenda.accent || 'sky';
    const selectedDensity = queryParams.value.density || props.agenda.density || 'medium';
    const selectedPreset = queryParams.value.preset || props.agenda.preset || 'clean';

    const shouldUseDark =
        selectedTheme === 'dark' ||
        (selectedTheme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    html.classList.toggle('theme-dark', shouldUseDark);
    html.dataset.theme = selectedTheme;
    html.dataset.density = selectedDensity;

    if (selectedAccent === 'honey') {
        html.style.setProperty('--za-primary', 'var(--honey-bronze)');
    } else if (selectedAccent === 'green') {
        html.style.setProperty('--za-primary', 'var(--green-yellow)');
    } else {
        html.style.setProperty('--za-primary', 'var(--sky-blue)');
    }

    const presetVars = presets[selectedPreset] || presets.clean;
    Object.entries(presetVars).forEach(([key, value]) => {
        html.style.setProperty(key, value);
    });
};

const fetchSlotsByDate = async (targetDate) => {
    if (!selectedServiceId.value) {
        return [];
    }

    const { data } = await axios.get(route('widget.availability', { providerProfile: props.provider.slug, agenda: props.agenda.slug }), {
        params: {
            service_id: selectedServiceId.value,
            date: targetDate,
        },
    });

    return data;
};

const loadSlots = async () => {
    selectedSlot.value = null;
    slots.value = await fetchSlotsByDate(date.value);
};

const prefetchMonthAvailability = async () => {
    if (!selectedServiceId.value) {
        availabilityByDate.value = {};

        return;
    }

    loadingMonth.value = true;
    const requestId = ++availabilityRequestId.value;

    const monthEntries = monthDays.value.filter(Boolean);
    const nextAvailability = {};

    for (const item of monthEntries) {
        if (item.isoDate < todayIso) {
            nextAvailability[item.isoDate] = 0;
            continue;
        }

        const daySlots = await fetchSlotsByDate(item.isoDate);
        nextAvailability[item.isoDate] = daySlots.length;
    }

    if (requestId !== availabilityRequestId.value) {
        return;
    }

    availabilityByDate.value = nextAvailability;
    loadingMonth.value = false;
};

const openSlotsModalForDate = async (targetDate) => {
    if ((availabilityByDate.value[targetDate] ?? 0) <= 0) {
        return;
    }

    date.value = targetDate;
    await loadSlots();
    slotsModalOpen.value = true;
};

const goPrevMonth = () => {
    monthCursor.value = new Date(monthCursor.value.getFullYear(), monthCursor.value.getMonth() - 1, 1);
};

const goNextMonth = () => {
    monthCursor.value = new Date(monthCursor.value.getFullYear(), monthCursor.value.getMonth() + 1, 1);
};

const continueFromStep1 = async () => {
    step.value = 2;
    await prefetchMonthAvailability();
};

const selectSlot = (slot) => {
    selectedSlot.value = slot;
    slotsModalOpen.value = false;
};

const continueFromStep2 = () => {
    if (!selectedSlot.value) {
        return;
    }

    step.value = 3;
};

const continueFromStep3 = async () => {
    const formData = new FormData();
    formData.append('service_id', String(selectedServiceId.value));
    formData.append('starts_at', selectedSlot.value.starts_at);
    formData.append('customer_name', booking.value.customer_name);
    formData.append('customer_email', booking.value.customer_email);
    formData.append('customer_phone', booking.value.customer_phone || '');
    formData.append('customer_notes', booking.value.customer_notes || '');

    booking.value.extra_fields.forEach((field, index) => {
        formData.append(`extra_fields[${index}][field_key]`, field.field_key);
        formData.append(`extra_fields[${index}][field_label]`, field.field_label);
        formData.append(`extra_fields[${index}][field_value]`, field.field_value || '');
    });

    selectedFiles.value.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    const { data } = await axios.post(route('widget.bookings.confirm', {
        providerProfile: props.provider.slug,
        agenda: props.agenda.slug,
    }), formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    });
    confirmation.value = data;
    step.value = 4;
};

const handleFilesChange = (event) => {
    selectedFiles.value = Array.from(event.target.files ?? []).slice(0, 5);
};

const copyPix = async () => {
    if (!confirmation.value?.payment?.qr_code_text) {
        return;
    }

    await navigator.clipboard.writeText(confirmation.value.payment.qr_code_text);
};

watch(monthCursor, async () => {
    if (step.value !== 2) {
        return;
    }

    await prefetchMonthAvailability();
});

watch(selectedServiceId, async () => {
    slots.value = [];
    selectedSlot.value = null;

    if (step.value !== 2) {
        return;
    }

    await prefetchMonthAvailability();
});

onMounted(() => {
    applyWidgetTheme();
});
</script>

<template>
    <Head :title="`Agendamento | ${provider.display_name} - ${agenda.name}`" />

    <div class="min-h-screen bg-[var(--za-bg)]">
        <div class="za-grid py-6 sm:py-8">
            <header class="mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-[var(--za-border)] pb-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.15em] text-[var(--za-text-muted)]">Widget Zenith</p>
                    <h1 class="font-[var(--za-font-display)] text-2xl sm:text-3xl">{{ provider.display_name }}</h1>
                    <p class="text-sm text-[var(--za-text-muted)]">{{ agenda.name }}</p>
                </div>
                <p class="za-chip">Etapa {{ step }}/4</p>
            </header>

            <section v-if="step === 1" class="space-y-4">
                <h2 class="font-[var(--za-font-display)] text-2xl">1. Selecione o serviço</h2>
                <div class="grid gap-3 md:grid-cols-2">
                    <button
                        v-for="service in services"
                        :key="service.id"
                        class="za-card p-4 text-left"
                        :class="selectedServiceId === service.id ? 'border-[var(--za-border-strong)]' : ''"
                        @click="selectedServiceId = service.id"
                    >
                        <h3 class="font-[var(--za-font-display)] text-xl">{{ service.name }}</h3>
                        <p class="mt-1 text-sm text-[var(--za-text-muted)]">{{ service.description }}</p>
                        <p class="mt-3 font-mono text-lg">R$ {{ (service.price_cents / 100).toFixed(2) }}</p>
                    </button>
                </div>
                <button class="za-button za-button-primary" @click="continueFromStep1">Continuar</button>
            </section>

            <section v-else-if="step === 2" class="space-y-5">
                <h2 class="font-[var(--za-font-display)] text-2xl">2. Escolha data e horário</h2>

                <div class="za-card p-4 sm:p-5">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <button class="za-button za-button-neutral" type="button" @click="goPrevMonth">←</button>
                        <p class="text-sm font-semibold capitalize">{{ monthTitle }}</p>
                        <button class="za-button za-button-neutral" type="button" @click="goNextMonth">→</button>
                    </div>

                    <div class="mb-2 grid grid-cols-7 gap-1 text-center text-xs uppercase tracking-[0.08em] text-[var(--za-text-muted)]">
                        <span v-for="label in dayLabels" :key="label">{{ label }}</span>
                    </div>

                    <div class="grid grid-cols-7 gap-1">
                        <template v-for="(dayItem, index) in monthDays" :key="dayItem ? dayItem.isoDate : `empty-${index}`">
                            <div v-if="!dayItem" class="h-14 border border-transparent"></div>
                            <button
                                v-else
                                type="button"
                                class="za-calendar-day"
                                :class="[
                                    date === dayItem.isoDate ? 'za-calendar-day-selected' : '',
                                    (availabilityByDate[dayItem.isoDate] ?? 0) > 0 ? 'za-calendar-day-available' : 'za-calendar-day-disabled',
                                ]"
                                :disabled="(availabilityByDate[dayItem.isoDate] ?? 0) === 0"
                                @click="openSlotsModalForDate(dayItem.isoDate)"
                            >
                                <span>{{ dayItem.day }}</span>
                                <span class="text-[10px] text-[var(--za-text-muted)]">
                                    <template v-if="typeof availabilityByDate[dayItem.isoDate] === 'number'">
                                        {{ availabilityByDate[dayItem.isoDate] }}
                                    </template>
                                    <template v-else>…</template>
                                </span>
                            </button>
                        </template>
                    </div>

                    <p v-if="loadingMonth" class="mt-3 text-xs text-[var(--za-text-muted)]">Carregando disponibilidade do mês...</p>
                </div>

                <div class="space-y-2">
                    <InputLabel value="Horário selecionado" />
                    <p class="text-sm text-[var(--za-text-muted)]">
                        <template v-if="selectedSlot">
                            {{ new Date(selectedSlot.starts_at).toLocaleDateString('pt-BR') }} às
                            {{ new Date(selectedSlot.starts_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}
                        </template>
                        <template v-else>
                            Clique em um dia verde para escolher o horário.
                        </template>
                    </p>
                </div>

                <div class="flex gap-2">
                    <button class="za-button za-button-neutral" @click="step = 1">Voltar</button>
                    <button class="za-button za-button-primary" @click="continueFromStep2">Continuar</button>
                </div>
            </section>

            <section v-else-if="step === 3" class="space-y-4">
                <h2 class="font-[var(--za-font-display)] text-2xl">3. Dados do cliente</h2>
                <div class="grid gap-3 md:grid-cols-2">
                    <div>
                        <InputLabel value="Nome completo" />
                        <input v-model="booking.customer_name" class="za-input" type="text" placeholder="Digite seu nome" />
                    </div>
                    <div>
                        <InputLabel value="E-mail" />
                        <input v-model="booking.customer_email" class="za-input" type="email" placeholder="Digite seu e-mail" />
                    </div>
                    <div>
                        <InputLabel value="Telefone" />
                        <input v-model="booking.customer_phone" class="za-input" type="text" placeholder="Digite seu telefone" />
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel value="Observações" />
                        <textarea
                            v-model="booking.customer_notes"
                            class="za-textarea"
                            rows="4"
                            placeholder="Descreva informações importantes para o atendimento"
                        ></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel value="Anexar documentos (até 5 arquivos)" />
                        <input
                            class="za-input"
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            @change="handleFilesChange"
                        />
                        <p v-if="selectedFiles.length" class="mt-2 text-xs text-[var(--za-text-muted)]">
                            {{ selectedFiles.length }} arquivo(s) selecionado(s)
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="za-button za-button-neutral" @click="step = 2">Voltar</button>
                    <button class="za-button za-button-primary" @click="continueFromStep3">Confirmar</button>
                </div>
            </section>

            <section v-else class="space-y-4">
                <h2 class="font-[var(--za-font-display)] text-2xl">4. Confirmação do agendamento</h2>
                <div class="za-card p-5">
                    <p><strong>Agenda:</strong> {{ agenda.name }}</p>
                    <p><strong>Serviço:</strong> {{ confirmation.service?.name }}</p>
                    <p><strong>Cliente:</strong> {{ confirmation.customer_name }}</p>
                    <p><strong>Início:</strong> {{ new Date(confirmation.starts_at).toLocaleString('pt-BR') }}</p>
                    <p><strong>Status:</strong> {{ confirmation.status }}</p>
                </div>

                <div v-if="isPaid && confirmation.payment" class="za-card space-y-3 p-5">
                    <p class="za-chip">Pagamento PIX</p>
                    <img :src="confirmation.payment.qr_code_image_url" alt="QR Code PIX" class="h-52 w-52 border border-[var(--za-border)] p-2" />
                    <div>
                        <InputLabel value="Código copia e cola" />
                        <textarea class="za-textarea" rows="3" readonly :value="confirmation.payment.qr_code_text"></textarea>
                    </div>
                    <button class="za-button za-button-primary" @click="copyPix">Copiar código PIX</button>
                </div>
            </section>
        </div>
    </div>

    <Modal :open="slotsModalOpen" @close="slotsModalOpen = false">
        <h3 class="mb-2 font-[var(--za-font-display)] text-2xl">Horários disponíveis</h3>
        <p class="mb-4 text-sm text-[var(--za-text-muted)]">{{ selectedDateLabel }}</p>

        <div class="grid gap-2 sm:grid-cols-2">
            <button
                v-for="slot in slots"
                :key="slot.starts_at"
                class="za-button za-button-neutral justify-start"
                @click="selectSlot(slot)"
            >
                {{ new Date(slot.starts_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}
            </button>
        </div>

        <p v-if="!slots.length" class="text-sm text-[var(--za-text-muted)]">Sem horários disponíveis para esta data.</p>

        <div class="mt-4 flex justify-end">
            <button class="za-button za-button-neutral" @click="slotsModalOpen = false">Fechar</button>
        </div>
    </Modal>
</template>

<style scoped>
.za-calendar-day {
    display: flex;
    height: 3.5rem;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--za-border);
    background: var(--za-surface);
    font-size: 0.75rem;
    transition: all var(--za-speed) var(--za-ease);
}

.za-calendar-day-available {
    border-color: color-mix(in srgb, var(--za-success) 55%, var(--twilight-indigo) 45%);
    background: color-mix(in srgb, var(--za-success) 22%, var(--za-surface) 78%);
}

.za-calendar-day-selected {
    border-width: 2px;
    border-color: color-mix(in srgb, var(--za-success) 70%, var(--twilight-indigo) 30%);
}

.za-calendar-day-disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>

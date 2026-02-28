<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/UI/Modal.vue';
import FeedbackModal from '@/Components/UI/FeedbackModal.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import {
    ChevronLeft,
    ChevronRight,
    Check,
    Clock,
    Calendar,
    User,
    Mail,
    Phone,
    FileText,
    Paperclip,
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Copy,
    Loader2,
    Scissors,
    CreditCard,
    Search,
    CalendarSearch,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    provider: Object,
    agenda: Object,
    services: Array,
});

// ── State ─────────────────────────────────────────────────────────────────────
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
const submitting = ref(false);
const copiedPix = ref(false);
const searchModalOpen = ref(false);
const searchForm = ref({ email: '', phone: '' });
const searchResults = ref([]);
const searching = ref(false);
const searchError = ref(null);
const reschedulingBooking = ref(null);

const feedback = ref({
    open: false,
    title: '',
    message: '',
    type: 'success',
    confirmText: 'OK',
    cancelText: 'Cancelar',
    onConfirm: null,
    onAction: null,
});

const showFeedback = (title, message, type = 'success', confirmText = 'OK', onConfirm = null, cancelText = 'Cancelar', onAction = null) => {
    feedback.value = { open: true, title, message, type, confirmText, onConfirm, cancelText, onAction };
};

const closeFeedback = () => {
    feedback.value.open = false;
};

const handleFeedbackConfirm = () => {
    if (feedback.value.onConfirm) {
        feedback.value.onConfirm();
    }
    closeFeedback();
};

const handleFeedbackAction = () => {
    if (feedback.value.onAction) {
        feedback.value.onAction();
    }
    closeFeedback();
};

const booking = ref({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    customer_notes: '',
    extra_fields: (props.agenda?.customer_extra_fields ?? []).slice(0, 3),
});
const confirmation = ref(null);

// ── Calendar ──────────────────────────────────────────────────────────────────
const dayLabels = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
const todayIso = new Date().toISOString().slice(0, 10);

const selectedService = computed(() => props.services.find((s) => s.id === selectedServiceId.value));
const isPaid = computed(() => ['full', 'half'].includes(props.agenda.payment_requirement) && (selectedService.value?.price_cents ?? 0) > 0);

const monthTitle = computed(() =>
    monthCursor.value.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }),
);

const selectedDateLabel = computed(() =>
    new Date(`${date.value}T00:00:00`).toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
    }),
);

const selectedSlotLabel = computed(() => {
    if (!selectedSlot.value) return null;
    const d = new Date(selectedSlot.value.starts_at);
    return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
});

const monthDays = computed(() => {
    const baseDate = new Date(monthCursor.value.getFullYear(), monthCursor.value.getMonth(), 1);
    const firstWeekday = baseDate.getDay();
    const daysInMonth = new Date(baseDate.getFullYear(), baseDate.getMonth() + 1, 0).getDate();
    const cells = [];

    for (let i = 0; i < firstWeekday; i++) cells.push(null);
    for (let day = 1; day <= daysInMonth; day++) {
        const fullDate = new Date(baseDate.getFullYear(), baseDate.getMonth(), day);
        cells.push({ day, isoDate: fullDate.toISOString().slice(0, 10) });
    }
    while (cells.length % 7 !== 0) cells.push(null);
    return cells;
});

// ── Theme ─────────────────────────────────────────────────────────────────────
const primaryColor = computed(() => props.agenda.primary_color || '#18181b');
const secondaryColor = computed(() => props.agenda.secondary_color || '#27272a');

const queryParams = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return {
        theme: params.get('theme'),
        accent: params.get('accent'),
        density: params.get('density'),
        preset: params.get('preset'),
        transparent_bg: params.get('transparent_bg') === '1' || props.agenda.transparent_bg,
        width: params.get('width') || props.agenda.embed_width || 480,
    };
});

const applyWidgetTheme = () => {
    const html = document.documentElement;
    const selectedTheme = queryParams.value.theme || props.agenda.theme || 'auto';
    const selectedDensity = queryParams.value.density || props.agenda.density || 'medium';

    const shouldUseDark =
        selectedTheme === 'dark' ||
        (selectedTheme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

    html.classList.toggle('theme-dark', shouldUseDark);
    html.dataset.theme = selectedTheme;
    html.dataset.density = selectedDensity;

    // Apply primary color to global styles if needed
    html.style.setProperty('--za-primary', primaryColor.value);
};

// ── Slots / Availability ──────────────────────────────────────────────────────
const fetchSlotsByDate = async (targetDate) => {
    if (!selectedServiceId.value) return [];
    const { data } = await axios.get(
        route('widget.availability', {
            providerProfile: props.provider.slug,
            agenda: props.agenda.slug,
        }),
        { params: { service_id: selectedServiceId.value, date: targetDate } },
    );
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

    if (requestId !== availabilityRequestId.value) return;

    availabilityByDate.value = nextAvailability;
    loadingMonth.value = false;
};

const openSlotsModalForDate = async (targetDate) => {
    if ((availabilityByDate.value[targetDate] ?? 0) <= 0) return;
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

// ── Navigation ────────────────────────────────────────────────────────────────
const continueFromStep1 = async () => {
    step.value = 2;
    await prefetchMonthAvailability();
};

const selectSlot = (slot) => {
    selectedSlot.value = slot;
    slotsModalOpen.value = false;
};

const continueFromStep2 = () => {
    if (!selectedSlot.value) return;
    step.value = 3;
};

const continueFromStep3 = async () => {
    submitting.value = true;
    try {
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

        const { data } = await axios.post(
            route('widget.bookings.confirm', {
                providerProfile: props.provider.slug,
                agenda: props.agenda.slug,
            }),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } },
        );

        confirmation.value = data;
        step.value = 4;
    } finally {
        submitting.value = false;
    }
};

const handleFilesChange = (event) => {
    selectedFiles.value = Array.from(event.target.files ?? []).slice(0, 5);
};

const copyPix = async () => {
    if (!confirmation.value?.payment?.qr_code_text) return;
    await navigator.clipboard.writeText(confirmation.value.payment.qr_code_text);
    copiedPix.value = true;
    setTimeout(() => (copiedPix.value = false), 2000);
};

const cancelBooking = async (bookingId) => {
    showFeedback(
        'Cancelar Agendamento',
        'Tem certeza que deseja cancelar este agendamento?',
        'confirm',
        'Manter Agendamento', // Primary (Safe)
        null, // onConfirm (just closes)
        'Sim, cancelar', // Secondary (Danger action)
        async () => {
            try {
                await axios.post(
                    route('widget.bookings.cancel', {
                        providerProfile: props.provider.slug,
                        agenda: props.agenda.slug,
                        booking: bookingId,
                    })
                );
                showFeedback('Sucesso', 'Agendamento cancelado com sucesso.');
                searchBookings();
            } catch (e) {
                showFeedback('Erro', e.response?.data?.message || 'Erro ao cancelar agendamento.', 'error');
            }
        }
    );
};

const initReschedule = (bookingObj) => {
    reschedulingBooking.value = bookingObj;
    selectedServiceId.value = bookingObj.service_id;
    searchModalOpen.value = false;
    step.value = 2; // Go to calendar
    prefetchMonthAvailability();
};

const finishReschedule = async () => {
    if (!selectedSlot.value || !reschedulingBooking.value) return;
    
    submitting.value = true;
    try {
        await axios.post(
            route('widget.bookings.reschedule', {
                providerProfile: props.provider.slug,
                agenda: props.agenda.slug,
                booking: reschedulingBooking.value.id,
            }),
            { starts_at: selectedSlot.value.starts_at }
        );
        
        showFeedback('Sucesso', 'Agendamento remarcado com sucesso.');
        reschedulingBooking.value = null;
        step.value = 1; 
    } catch (e) {
        showFeedback('Erro', e.response?.data?.message || 'Erro ao remarcar agendamento.', 'error');
    } finally {
        submitting.value = false;
    }
};

const searchBookings = async () => {
    searching.value = true;
    searchError.value = null;
    searchResults.value = [];
    try {
        const { data } = await axios.post(
            route('widget.bookings.search', {
                providerProfile: props.provider.slug,
                agenda: props.agenda.slug,
            }),
            searchForm.value
        );
        searchResults.value = data;
        if (data.length === 0) {
            searchError.value = 'Nenhum agendamento encontrado.';
        }
    } catch (e) {
        searchError.value = e.response?.data?.message || 'Erro ao buscar agendamentos.';
    } finally {
        searching.value = false;
    }
};

const formatShortDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// ── Price format ──────────────────────────────────────────────────────────────
const formatPrice = (cents) =>
    (cents / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

watch(monthCursor, async () => {
    if (step.value !== 2) return;
    await prefetchMonthAvailability();
});

watch(selectedServiceId, async () => {
    slots.value = [];
    selectedSlot.value = null;
    if (step.value !== 2) return;
    await prefetchMonthAvailability();
});

onMounted(() => {
    applyWidgetTheme();
    if (queryParams.value.transparent_bg) {
        document.body.style.backgroundColor = 'transparent';
        document.documentElement.style.backgroundColor = 'transparent';
        document.body.classList.add('bg-transparent');
    }
});
</script>

<template>
    <Head :title="`Agendamento | ${provider.display_name} — ${agenda.name}`" />

    <div 
        class="min-h-screen flex flex-col items-center p-4"
        :class="queryParams.transparent_bg ? 'bg-transparent' : 'bg-background text-foreground'"
    >

        <!-- ── Top Banner ─────────────────────────────────────────────────── -->
        <div 
            class="w-full mb-6 rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center justify-between group transition-all"
            :class="queryParams.transparent_bg ? 'bg-white/5 backdrop-blur-md border-white/10' : 'bg-white'"
            :style="{ maxWidth: `${queryParams.width}px` }"
        >
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-slate-50 flex items-center justify-center border border-slate-100 transition-colors group-hover:bg-slate-100">
                    <CalendarSearch class="h-5 w-5 text-slate-400" />
                </div>
                <p class="text-[11px] font-medium text-slate-500 max-w-[170px] leading-tight">
                    Para consultar, remarcar ou cancelar um agendamento
                </p>
            </div>
            <button 
                @click="searchModalOpen = true"
                class="bg-black text-white px-4 py-2.5 rounded-lg text-[11px] font-bold hover:bg-slate-800 transition-all shadow-lg shadow-black/5 active:scale-95"
            >
                Meus Agendamentos
            </button>
        </div>

        <!-- ── Main Widget Container (Phone-like Mockup Style) ──────────────── -->
        <div 
            class="w-full sm:my-8 sm:rounded-[2.5rem] overflow-hidden flex flex-col transition-all duration-500 min-h-[500px]"
            :class="[
                queryParams.transparent_bg ? 'bg-transparent shadow-none border-none' : 'bg-background shadow-2xl border border-border'
            ]"
            :style="{ maxWidth: `${queryParams.width}px` }"
        >
            
            <!-- ── Header (Identical to Provider Mockup) ────────────────────── -->
            <header 
                class="px-8 py-8 text-white flex items-center gap-5 transition-all duration-500 shrink-0" 
                :class="{ 'rounded-t-[2.5rem]': !queryParams.transparent_bg }"
                :style="{ backgroundColor: primaryColor }"
            >
                <div class="h-12 w-12 rounded-full bg-white/10 flex items-center justify-center text-xl font-bold text-white shadow-inner">
                    {{ agenda.name ? agenda.name.charAt(0).toUpperCase() : 'B' }}
                </div>
                <div>
                    <h3 class="text-base font-bold leading-tight">{{ agenda.name || 'Minha Agenda' }}</h3>
                    <p class="text-white/60 text-[11px] font-medium">{{ provider.display_name || 'Profissional' }}</p>
                </div>
            </header>

            <!-- ── Content ──────────────────────────────────────────────────── -->
            <div 
                class="flex-1 p-6 flex flex-col relative"
                :class="{ 'bg-background/20 backdrop-blur-sm rounded-b-[2.5rem]': queryParams.transparent_bg }"
            >

                <!-- ── Progress Stepper (Identical to Provider Mockup) ───────── -->
                <div class="flex items-center gap-2 mb-8 select-none">
                    <!-- Step 1 -->
                    <div 
                        class="h-7 w-7 rounded-full flex items-center justify-center text-[10px] font-bold transition-all shrink-0"
                        :class="step > 1 ? 'text-white' : 'bg-black text-white'"
                        :style="step > 1 ? { backgroundColor: primaryColor } : {}"
                    >
                        <Check v-if="step > 1" class="h-3.5 w-3.5" />
                        <span v-else>1</span>
                    </div>
                    <div class="h-[1px] flex-1 bg-muted"></div>
                    
                    <!-- Step 2 -->
                    <div 
                        class="h-7 w-7 rounded-full flex items-center justify-center text-[10px] font-bold transition-all shrink-0"
                        :class="[
                            step > 2 ? 'text-white' : step === 2 ? 'bg-black text-white' : 'border border-border text-muted-foreground'
                        ]"
                        :style="step > 2 ? { backgroundColor: primaryColor } : {}"
                    >
                        <Check v-if="step > 2" class="h-3.5 w-3.5" />
                        <span v-else>2</span>
                    </div>
                    <div class="h-[1px] flex-1 bg-muted"></div>
                    
                    <!-- Step 3 -->
                    <div 
                        class="h-7 w-7 rounded-full flex items-center justify-center text-[10px] font-bold transition-all shrink-0"
                        :class="[
                            step > 3 ? 'text-white' : step === 3 ? 'bg-black text-white' : 'border border-border text-muted-foreground'
                        ]"
                        :style="step > 3 ? { backgroundColor: primaryColor } : {}"
                    >
                        <Check v-if="step > 3" class="h-3.5 w-3.5" />
                        <span v-else>3</span>
                    </div>
                    <div class="h-[1px] flex-1 bg-muted"></div>
                    
                    <!-- Step 4 -->
                    <div 
                        class="h-7 w-7 rounded-full flex items-center justify-center text-[10px] font-bold transition-all shrink-0"
                        :class="[
                            step === 4 ? 'bg-black text-white' : 'border border-border text-muted-foreground'
                        ]"
                        :style="step === 4 ? { backgroundColor: primaryColor } : {}"
                    >
                        4
                    </div>
                    
                    <span class="text-[9px] uppercase font-bold text-muted-foreground ml-2 tracking-widest whitespace-nowrap">
                        {{ step === 1 ? 'Serviços' : step === 2 ? 'Horário' : step === 3 ? 'Dados' : 'Confirmação' }}
                    </span>
                </div>

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- STEP 1 — Serviço                                           -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <section v-if="step === 1" class="flex-1 flex flex-col animate-in fade-in duration-300">
                    <div class="flex items-center gap-2 mb-6">
                        <Scissors class="h-3.5 w-3.5 text-muted-foreground" />
                        <span class="text-[10px] uppercase font-bold text-muted-foreground tracking-wider">Selecione o serviço</span>
                    </div>

                    <div class="flex-1 space-y-3">
                        <div 
                            v-for="service in services" 
                            :key="service.id" 
                            @click="selectedServiceId = service.id"
                            class="p-4 rounded-xl border transition-all cursor-pointer flex justify-between items-center shadow-sm"
                            :class="[
                                selectedServiceId === service.id 
                                    ? 'bg-muted border-transparent' 
                                    : 'border-border bg-card hover:bg-muted/50'
                            ]"
                            :style="selectedServiceId === service.id ? { borderColor: primaryColor, boxShadow: `0 0 0 1px ${primaryColor}` } : {}"
                        >
                            <div class="flex items-center gap-3">
                                <div 
                                    class="h-5 w-5 rounded border flex items-center justify-center transition-all" 
                                    :class="selectedServiceId === service.id ? 'border-transparent' : 'border-border'"
                                    :style="selectedServiceId === service.id ? { backgroundColor: primaryColor } : {}"
                                >
                                    <Check v-if="selectedServiceId === service.id" class="h-3 w-3 text-white" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-foreground">{{ service.name }}</span>
                                    <span class="text-[10px] text-muted-foreground">{{ service.duration_minutes ?? 60 }}min</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold" :class="service.price_cents === 0 ? 'text-emerald-600' : 'text-foreground'">
                                {{ service.price_cents === 0 ? 'Gratuito' : formatPrice(service.price_cents) }}
                            </span>
                        </div>
                    </div>

                    <div v-if="selectedServiceId" class="mt-8">
                        <button 
                            @click="continueFromStep1" 
                            class="w-full h-12 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 hover:opacity-90" 
                            :style="{ backgroundColor: primaryColor }"
                        >
                            Continuar
                        </button>
                    </div>
                    <div v-else class="mt-8 flex justify-end opacity-30">
                        <span class="text-[8px] text-muted-foreground font-bold uppercase tracking-widest">Powered by zenith</span>
                    </div>
                </section>

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- STEP 2 — Data e horário                                    -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <section v-else-if="step === 2" class="flex-1 flex flex-col animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center gap-2 mb-6 text-muted-foreground">
                        <button @click="step = 1" class="hover:text-foreground transition-colors"><ChevronLeft class="h-4 w-4" /></button>
                        <Calendar class="h-4 w-4" />
                        <span class="text-[10px] uppercase font-bold tracking-wider">Escolha a data e horário</span>
                    </div>

                    <div class="bg-muted/30 rounded-[2rem] p-6 mb-6">
                        <!-- Month nav -->
                        <div class="mb-6 flex items-center justify-between px-2">
                            <span class="text-sm font-bold text-foreground capitalize">{{ monthTitle }}</span>
                            <div class="flex gap-4 text-muted-foreground">
                                <button type="button" @click="goPrevMonth" class="hover:text-foreground"><ChevronLeft class="h-4 w-4" /></button>
                                <button type="button" @click="goNextMonth" class="hover:text-foreground"><ChevronRight class="h-4 w-4" /></button>
                            </div>
                        </div>

                        <!-- Days labels -->
                        <div class="mb-4 grid grid-cols-7 gap-2 text-center">
                            <span v-for="d in ['D','S','T','Q','Q','S','S']" :key="d" class="text-[10px] font-bold text-muted-foreground/50">{{ d }}</span>
                        </div>

                        <!-- Days grid -->
                        <div class="grid grid-cols-7 gap-2">
                            <template v-for="(dayItem, index) in monthDays" :key="dayItem ? dayItem.isoDate : `empty-${index}`">
                                <div v-if="!dayItem" class="h-9" />
                                <button 
                                    v-else
                                    type="button"
                                    :disabled="(availabilityByDate[dayItem.isoDate] ?? 0) === 0"
                                    @click="openSlotsModalForDate(dayItem.isoDate)"
                                    class="h-9 flex items-center justify-center text-[11px] font-bold rounded-xl transition-all"
                                    :class="[
                                        date === dayItem.isoDate ? 'text-white shadow-md' : (availabilityByDate[dayItem.isoDate] ?? 0) > 0 ? 'text-foreground hover:bg-muted cursor-pointer' : 'text-muted-foreground/30 border-transparent cursor-not-allowed'
                                    ]"
                                    :style="date === dayItem.isoDate ? { backgroundColor: primaryColor } : {}"
                                >
                                    {{ dayItem.day }}
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Selected slot recap -->
                    <div v-if="selectedSlot" class="mb-6 p-4 rounded-xl border border-foreground/10 bg-foreground/5 flex items-center gap-3">
                        <Clock class="h-4 w-4 text-foreground/60" />
                        <div>
                            <p class="text-xs font-bold text-foreground">{{ selectedDateLabel }}</p>
                            <p class="text-[10px] text-muted-foreground">às {{ selectedSlotLabel }}</p>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <button 
                            @click="reschedulingBooking ? finishReschedule() : continueFromStep2()" 
                            :disabled="!selectedSlot || submitting"
                            class="w-full h-12 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 hover:opacity-90 disabled:opacity-40 flex items-center justify-center gap-2" 
                            :style="{ backgroundColor: primaryColor }"
                        >
                            <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                            {{ reschedulingBooking ? 'Confirmar Reagendamento' : 'Continuar' }}
                        </button>
                        <button 
                            v-if="reschedulingBooking"
                            @click="reschedulingBooking = null; step = 1"
                            class="w-full mt-2 h-10 text-muted-foreground text-[10px] font-bold uppercase tracking-widest hover:text-foreground"
                        >
                            Cancelar Reagendamento
                        </button>
                    </div>
                </section>

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- STEP 3 — Dados do cliente                                  -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <section v-else-if="step === 3" class="flex-1 flex flex-col animate-in slide-in-from-right-4 duration-300">
                    <div class="flex items-center gap-2 mb-6 text-muted-foreground">
                        <button @click="step = 2" class="hover:text-foreground transition-colors"><ChevronLeft class="h-4 w-4" /></button>
                        <User class="h-4 w-4" />
                        <span class="text-[10px] uppercase font-bold tracking-wider">Confirme seus dados</span>
                    </div>

                    <!-- Summary Card & Form in Two Columns -->
                    <div class="grid gap-6 items-start" :class="queryParams.width > 580 ? 'grid-cols-2' : 'grid-cols-1'">
                        <!-- Summary Column -->
                        <div class="rounded-2xl border border-border p-6 shadow-sm" :class="queryParams.transparent_bg ? 'bg-transparent' : 'bg-card'">
                            <div class="flex items-center gap-3 mb-6 text-muted-foreground">
                                <Calendar class="h-4 w-4" />
                                <span class="text-[11px] font-bold text-foreground">Detalhes do agendamento</span>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-2">SERVIÇO</span>
                                    <div class="flex justify-between items-center text-left">
                                        <span class="text-xs font-medium text-foreground">{{ selectedService?.name }}</span>
                                        <span class="text-xs font-bold" :class="selectedService?.price_cents === 0 ? 'text-emerald-600' : 'text-foreground'">
                                            {{ selectedService?.price_cents === 0 ? 'Gratuito' : formatPrice(selectedService?.price_cents) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="h-[1px] bg-border/50"></div>
                                <div class="grid grid-cols-2 gap-4 text-left">
                                    <div>
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">DATA</span>
                                        <span class="text-xs font-bold text-foreground">{{ selectedDateLabel }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest block mb-1">HORÁRIO</span>
                                        <span class="text-xs font-bold text-foreground">{{ selectedSlotLabel }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Column -->
                        <div class="space-y-4">
                            <div class="grid gap-4" :class="{ 'grid-cols-2': queryParams.width > 750 }">
                                <div class="space-y-1.5 text-left">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Nome completo</label>
                                    <input v-model="booking.customer_name" type="text" placeholder="Seu nome" class="w-full h-10 px-4 rounded-lg bg-muted border-none text-xs font-medium outline-none" />
                                </div>
                                <div class="space-y-1.5 text-left">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">E-mail</label>
                                    <input v-model="booking.customer_email" type="email" placeholder="seu@email.com" class="w-full h-10 px-4 rounded-lg bg-muted border-none text-xs font-medium outline-none" />
                                </div>
                                <div class="space-y-1.5 text-left">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Telefone</label>
                                    <input v-model="booking.customer_phone" type="tel" placeholder="(00) 00000-0000" class="w-full h-10 px-4 rounded-lg bg-muted border-none text-xs font-medium outline-none" />
                                </div>
                                
                                <!-- Custom Fields -->
                                <div v-for="field in booking.extra_fields" :key="field.field_key" class="space-y-1.5 text-left">
                                    <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">{{ field.field_label }}</label>
                                    <input v-model="field.field_value" type="text" class="w-full h-10 px-4 rounded-lg bg-muted border-none text-xs font-medium outline-none" />
                                </div>
                            </div>

                            <div v-if="agenda.embed_height > 0" class="space-y-1.5 text-left">
                                <label class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Anexos (opcional)</label>
                                <label class="w-full h-10 px-4 rounded-lg bg-muted border border-dashed border-border flex items-center gap-2 cursor-pointer transition-colors hover:bg-muted/80">
                                    <Paperclip class="h-3.5 w-3.5 text-muted-foreground" />
                                    <span class="text-[10px] text-muted-foreground truncate">{{ selectedFiles.length ? selectedFiles.length + ' arquivo(s)' : 'Selecionar arquivos' }}</span>
                                    <input type="file" multiple class="hidden" @change="handleFilesChange" />
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto space-y-4">
                        <button 
                            @click="continueFromStep3" 
                            :disabled="submitting || !booking.customer_name || !booking.customer_email || !booking.customer_phone"
                            class="w-full h-12 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 hover:opacity-90 disabled:opacity-40 flex items-center justify-center gap-2" 
                            :style="{ backgroundColor: primaryColor }"
                        >
                            <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                            Confirmar Agendamento
                        </button>
                        <div class="flex justify-between items-center text-[9px] font-bold text-muted-foreground uppercase tracking-widest px-2 group">
                            <span class="opacity-30 group-hover:opacity-100 transition-opacity">Powered by zenith</span>
                        </div>
                    </div>
                </section>

                <!-- ═══════════════════════════════════════════════════════════ -->
                <!-- STEP 4 — Confirmação                                       -->
                <!-- ═══════════════════════════════════════════════════════════ -->
                <section v-else class="flex-1 flex flex-col items-center justify-center text-center animate-in zoom-in duration-500">
                    <div class="h-20 w-20 rounded-full flex items-center justify-center mb-8 shadow-lg" :style="{ backgroundColor: primaryColor + '15' }">
                        <CheckCircle2 class="h-10 w-10" :style="{ color: primaryColor }" />
                    </div>
                    <h3 class="text-xl font-bold text-foreground mb-3">Tudo pronto!</h3>
                    <p class="text-[13px] text-muted-foreground leading-relaxed mb-8 px-4">
                        Seu agendamento para <strong>{{ selectedDateLabel }}</strong> às <strong>{{ selectedSlotLabel }}</strong> foi realizado com sucesso.
                    </p>
                    
                    <!-- PIX Payment -->
                    <div v-if="isPaid && confirmation?.payment" class="w-full p-5 rounded-2xl bg-muted/40 border border-border mb-8 text-left">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-6 w-6 rounded-full bg-foreground/10 flex items-center justify-center">
                                <CreditCard class="h-3 w-3 text-foreground" />
                            </div>
                            <span class="text-xs font-bold text-foreground">Pagamento via PIX</span>
                        </div>
                        
                        <div class="flex flex-col items-center gap-4">
                            <img :src="confirmation.payment.qr_code_image_url" alt="QR Code" class="h-32 w-32 border border-border rounded-lg bg-white p-2" />
                            <div class="w-full space-y-2">
                                <button 
                                    @click="copyPix" 
                                    class="w-full py-2.5 rounded-lg text-[11px] font-bold transition-all flex items-center justify-center gap-2"
                                    :class="copiedPix ? 'bg-emerald-600 text-white' : 'bg-foreground text-background'"
                                >
                                    <Check v-if="copiedPix" class="h-3.5 w-3.5" />
                                    <Copy v-else class="h-3.5 w-3.5" />
                                    {{ copiedPix ? 'Código copiado!' : 'Copiar código PIX' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <button 
                        @click="step = 1; confirmation = null;" 
                        class="px-8 py-3 rounded-xl border border-border text-[10px] font-bold text-muted-foreground hover:text-foreground hover:border-foreground transition-all uppercase tracking-widest"
                    >
                        Novo Agendamento
                    </button>
                </section>

            </div>
        </div>
    </div>

    <!-- ── Slots Modal ─────────────────────────────────────────────────────── -->
    <Modal :open="slotsModalOpen" @close="slotsModalOpen = false">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg flex items-center justify-center" :style="{ backgroundColor: primaryColor + '10' }">
                    <Clock class="h-4 w-4" :style="{ color: primaryColor }" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-foreground">Horários disponíveis</h3>
                    <p class="text-[11px] text-muted-foreground capitalize">{{ selectedDateLabel }}</p>
                </div>
            </div>

            <div v-if="slots.length" class="grid grid-cols-2 gap-2 sm:grid-cols-3 pt-2">
                <button
                    v-for="slot in slots"
                    :key="slot.starts_at"
                    type="button"
                    @click="selectSlot(slot)"
                    class="flex items-center justify-center gap-2 rounded-xl border px-3 py-3 text-xs font-bold transition-all"
                    :class="[
                        selectedSlot?.starts_at === slot.starts_at
                            ? 'border-transparent text-white shadow-md'
                            : 'border-border bg-card text-foreground hover:border-muted-foreground/40 hover:bg-muted'
                    ]"
                    :style="selectedSlot?.starts_at === slot.starts_at ? { backgroundColor: primaryColor } : {}"
                >
                    {{ new Date(slot.starts_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}
                </button>
            </div>

            <p v-else class="text-sm text-muted-foreground py-4 text-center">
                Nenhum horário disponível para essa data.
            </p>

            <div class="flex justify-end pt-4">
                <button
                    type="button"
                    @click="slotsModalOpen = false"
                    class="text-xs font-bold text-muted-foreground hover:text-foreground transition-colors uppercase tracking-widest"
                >
                    Fechar
                </button>
            </div>
        </div>
    </Modal>



    <!-- ── Search Modal ─────────────────────────────────────────────────────── -->
    <Modal :open="searchModalOpen" @close="searchModalOpen = false">
        <div class="-m-6 flex flex-col">
            <!-- Header -->
            <div class="bg-[#18181b] text-white p-6 relative">
                <button @click="searchModalOpen = false" class="absolute right-4 top-4 text-white/60 hover:text-white transition-colors">
                    <X class="h-5 w-5" />
                </button>
                <h3 class="text-base font-bold">Meus Agendamentos</h3>
                <p class="text-xs text-white/60">Busque pelo e-mail ou telefone</p>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-6">
                <div class="flex items-center gap-2 mb-2">
                    <Search class="h-4 w-4 text-slate-400" />
                    <span class="text-xs font-bold text-slate-900">Localizar agendamento</span>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <Mail class="h-3 w-3" /> E-mail
                        </label>
                        <input 
                            v-model="searchForm.email"
                            type="email" 
                            placeholder="seu@email.com" 
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-white text-sm focus:border-black focus:ring-0 transition-all"
                        />
                    </div>

                    <div class="relative flex items-center justify-center py-2">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-100"></div></div>
                        <span class="relative bg-white px-3 text-[10px] font-bold text-slate-300 uppercase">ou</span>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <Phone class="h-3 w-3" /> Telefone
                        </label>
                        <input 
                            v-model="searchForm.phone"
                            type="tel" 
                            placeholder="(11) 99999-0000" 
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 bg-white text-sm focus:border-black focus:ring-0 transition-all"
                        />
                    </div>
                </div>

                <div v-if="searchError" class="p-3 rounded-lg bg-rose-50 border border-rose-100 text-rose-600 text-[11px] font-medium">
                    {{ searchError }}
                </div>

                <!-- Results -->
                <div v-if="searchResults.length" class="space-y-3 pt-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Agendamentos encontrados</p>
                    <div v-for="res in searchResults" :key="res.id" class="p-4 rounded-xl border border-slate-100 bg-white shadow-sm space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ res.service?.name }}</p>
                                <div class="flex items-center gap-1.5 mt-1 text-slate-500">
                                    <Clock class="h-3 w-3" />
                                    <span class="text-[11px] font-medium">{{ formatShortDate(res.starts_at) }}</span>
                                </div>
                            </div>
                            <span 
                                class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase"
                                :class="{
                                    'bg-amber-100 text-amber-700': res.status === 'pending',
                                    'bg-sky-100 text-sky-700': res.status === 'confirmed',
                                }"
                            >
                                {{ res.status === 'pending' ? 'Pendente' : 'Confirmado' }}
                            </span>
                        </div>
                        
                        <div class="flex gap-2 pt-1">
                            <button 
                                @click="cancelBooking(res.id)"
                                class="flex-1 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-slate-600 hover:bg-slate-50 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button 
                                @click="initReschedule(res)"
                                class="flex-1 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-slate-600 hover:bg-slate-50 transition-colors"
                            >
                                Remarcar
                            </button>
                        </div>
                    </div>
                </div>

                <button 
                    @click="searchBookings"
                    :disabled="searching || (!searchForm.email && !searchForm.phone)"
                    class="w-full h-12 bg-black text-white rounded-xl text-sm font-bold shadow-lg hover:bg-slate-800 transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                >
                    <Loader2 v-if="searching" class="h-4 w-4 animate-spin" />
                    Buscar agendamentos
                </button>

                <div class="flex justify-start">
                    <a 
                        href="https://zenithagenda.com.br/" 
                        target="_blank" 
                        class="text-[8px] font-bold text-slate-300 uppercase tracking-widest hover:text-slate-500 transition-colors"
                    >
                        feito Por zenith agenda
                    </a>
                </div>
            </div>
        </div>
    </Modal>

    <!-- ── Feedback Modal ───────────────────────────────────────────────────── -->
    <FeedbackModal
        :open="feedback.open"
        :title="feedback.title"
        :message="feedback.message"
        :type="feedback.type"
        :confirm-text="feedback.confirmText"
        :cancel-text="feedback.cancelText"
        @close="closeFeedback"
        @confirm="handleFeedbackConfirm"
        @action="handleFeedbackAction"
    />
</template>

<style scoped>
.min-h-screen {
    min-height: 100vh;
    min-height: 100dvh;
}

@media (min-width: 640px) {
    .min-h-screen {
        min-height: auto;
    }
}
</style>

<style>
/* CSS Variables for Zenit Aesthetics */
:root {
    --za-font-display: 'Inter', sans-serif;
}
</style>

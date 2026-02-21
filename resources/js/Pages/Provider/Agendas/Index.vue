<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NovaAgendaForm from '@/Components/Provider/NovaAgendaForm.vue';
import Modal from '@/Components/Modal.vue';
import { cn } from '@/lib/utils';
import {
  Plus,
  MoreHorizontal,
  Clock,
  Pencil,
  Trash2,
  Copy,
  Link,
  Code,
  ToggleLeft,
  ToggleRight,
  Check,
  X
} from "lucide-vue-next";

const props = defineProps({
    agendas: {
        type: Array,
        default: () => [],
    },
    providerProfile: {
        type: Object,
        required: true
    }
});

const openMenu = ref(null);
const showNewForm = ref(false);
const showEmbedModal = ref(false);
const embedCode = ref('');
const copiedLink = ref(null);
const copiedEmbed = ref(false);

const WEEKDAY_LABELS = {
  seg: "Seg",
  ter: "Ter",
  qua: "Qua",
  qui: "Qui",
  sex: "Sex",
  sab: "Sab",
  dom: "Dom",
};

const WEEKDAY_MAP = {
    1: 'seg',
    2: 'ter',
    3: 'qua',
    4: 'qui',
    5: 'sex',
    6: 'sab',
    0: 'dom'
};

const schedules = computed(() => {
    return props.agendas.map(agenda => {
        const weekdays = [...new Set(agenda.availability_rules?.map(rule => WEEKDAY_MAP[rule.weekday]))].filter(Boolean);
        const firstRule = agenda.availability_rules?.[0];
        
        return {
            id: agenda.id,
            name: agenda.name,
            professional: agenda.provider_profile?.user?.name || 'Profissional', // Ajustar conforme o modelo
            weekdays: weekdays,
            startTime: firstRule ? firstRule.starts_at.substring(0, 5) : '08:00',
            endTime: firstRule ? firstRule.ends_at.substring(0, 5) : '18:00',
            interval: 30, // Mock ou buscar de config
            services: agenda.services?.map(s => ({
                id: s.id,
                name: s.name,
                price: s.price_cents / 100,
                isFree: s.price_cents === 0
            })) || [],
            active: agenda.is_published,
            slug: agenda.slug
        };
    });
});

const toggleActive = (id) => {
    useForm({}).post(route('provider.agendas.publish', id), {
        preserveScroll: true,
        onSuccess: () => {
            openMenu.value = null;
        }
    });
};

const deleteAgenda = (id) => {
    if (confirm('Tem certeza?')) {
        useForm({}).delete(route('provider.agendas.destroy', id), {
            preserveScroll: true
        });
    }
};

const copyLink = (schedule) => {
    const url = route('widget.show', { 
        providerProfile: props.providerProfile.slug, 
        agenda: schedule.slug 
    });
    
    navigator.clipboard.writeText(url);
    copiedLink.value = schedule.id;
    
    setTimeout(() => {
        copiedLink.value = null;
        openMenu.value = null;
    }, 1000);
};

const openEmbedModalFn = (schedule) => {
    const url = route('widget.show', { 
        providerProfile: props.providerProfile.slug, 
        agenda: schedule.slug 
    });
    
    embedCode.value = `<iframe
  src="${url}"
  width="100%"
  height="700"
  frameborder="0"
  allow="payment"
></iframe>`;
    
    showEmbedModal.value = true;
    openMenu.value = null;
};

const copyEmbedCode = () => {
    navigator.clipboard.writeText(embedCode.value);
    copiedEmbed.value = true;
    setTimeout(() => copiedEmbed.value = false, 2000);
};

// Stats
const stats = computed(() => [
    { label: 'Agendas ativas', value: schedules.value.filter(s => s.active).length },
    { label: 'Profissionais', value: new Set(schedules.value.map(s => s.professional)).size },
    { label: 'Total de agendas', value: schedules.value.length },
]);
</script>

<template>
    <Head title="Gestão de agendas" />

    <AuthenticatedLayout>
        <template v-if="showNewForm">
            <NovaAgendaForm 
                @close="showNewForm = false" 
            />
        </template>

        <template v-else>
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-foreground">
                        Gestão de Agendas
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Configure horários de atendimento por profissional.
                    </p>
                </div>
                <button
                    @click="showNewForm = true"
                    class="flex items-center gap-2 self-start rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Nova agenda
                </button>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div v-for="stat in stats" :key="stat.label" class="rounded-lg border border-border bg-card p-5">
                    <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                        {{ stat.label }}
                    </span>
                    <p class="mt-2 text-2xl font-semibold text-foreground">
                        {{ stat.value }}
                    </p>
                </div>
            </div>

            <!-- Schedule cards -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div
                    v-for="schedule in schedules"
                    :key="schedule.id"
                    :class="cn(
                        'rounded-lg border border-border bg-card transition-opacity',
                        !schedule.active && 'opacity-60'
                    )"
                >
                    <div class="flex items-start justify-between border-b border-border px-5 py-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-medium text-foreground">
                                    {{ schedule.name }}
                                </h3>
                                <span
                                    :class="cn(
                                        'rounded-full px-2 py-0.5 text-[10px] font-medium',
                                        schedule.active
                                            ? 'bg-emerald-500/10 text-emerald-600'
                                            : 'bg-muted text-muted-foreground'
                                    )"
                                >
                                    {{ schedule.active ? "Ativo" : "Inativo" }}
                                </span>
                            </div>
                            <span class="text-xs text-muted-foreground">
                                {{ schedule.professional }}
                            </span>
                        </div>
                        <div class="relative">
                            <button
                                @click="openMenu = openMenu === schedule.id ? null : schedule.id"
                                class="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                                aria-label="Opções"
                            >
                                <MoreHorizontal class="h-4 w-4" />
                            </button>
                            <div v-if="openMenu === schedule.id" class="absolute right-0 top-9 z-10 w-48 rounded-md border border-border bg-popover py-1 shadow-lg">
                                <button
                                    @click="openMenu = null"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-xs text-foreground transition-colors hover:bg-accent"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                    Editar
                                </button>
                                <button
                                    @click="openMenu = null"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-xs text-foreground transition-colors hover:bg-accent"
                                >
                                    <Copy class="h-3.5 w-3.5" />
                                    Duplicar
                                </button>
                                <button
                                    @click="copyLink(schedule)"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-xs text-foreground transition-colors hover:bg-accent"
                                >
                                    <template v-if="copiedLink === schedule.id">
                                        <Check class="h-3.5 w-3.5 text-emerald-500" />
                                        <span class="text-emerald-500 font-medium">Copiado!</span>
                                    </template>
                                    <template v-else>
                                        <Link class="h-3.5 w-3.5" />
                                        Copiar link
                                    </template>
                                </button>
                                <button
                                    @click="openEmbedModalFn(schedule)"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-xs text-foreground transition-colors hover:bg-accent"
                                >
                                    <Code class="h-3.5 w-3.5" />
                                    Obter código
                                </button>
                                <button
                                    @click="toggleActive(schedule.id)"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-xs text-foreground transition-colors hover:bg-accent"
                                >
                                    <component :is="schedule.active ? ToggleLeft : ToggleRight" class="h-3.5 w-3.5" />
                                    {{ schedule.active ? "Desativar" : "Ativar" }}
                                </button>
                                <div class="my-1 border-t border-border" />
                                <button
                                    @click="deleteAgenda(schedule.id)"
                                    class="flex w-full items-center gap-2 px-3 py-2 text-xs text-destructive transition-colors hover:bg-accent"
                                >
                                    <Trash2 class="h-3.5 w-3.5" />
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-4">
                        <!-- Weekdays -->
                        <div class="mb-4">
                            <span class="mb-2 block text-xs text-muted-foreground">
                                Dias da semana
                            </span>
                            <div class="flex gap-1.5">
                                <span
                                    v-for="(label, key) in WEEKDAY_LABELS"
                                    :key="key"
                                    :class="cn(
                                        'flex h-7 w-9 items-center justify-center rounded text-[10px] font-medium',
                                        schedule.weekdays.includes(key)
                                            ? 'bg-primary text-primary-foreground'
                                            : 'bg-muted text-muted-foreground'
                                    )"
                                >
                                    {{ label }}
                                </span>
                            </div>
                        </div>

                        <!-- Time info -->
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <Clock class="h-3.5 w-3.5 text-muted-foreground" />
                                <span class="text-xs text-foreground">
                                    {{ schedule.startTime }} - {{ schedule.endTime }}
                                </span>
                            </div>
                            <div class="h-3 w-px bg-border" />
                            <span class="text-xs text-muted-foreground">
                                Intervalo: {{ schedule.interval }}min
                            </span>
                        </div>

                        <!-- Services -->
                        <div v-if="schedule.services && schedule.services.length > 0" class="mt-4 border-t border-border pt-3">
                            <span class="mb-2 block text-xs text-muted-foreground">
                                Serviços ({{ schedule.services.length }})
                            </span>
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="service in schedule.services"
                                    :key="service.id"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-secondary px-2 py-1 text-[10px] font-medium text-secondary-foreground"
                                >
                                    {{ service.name }}
                                    <span :class="cn(
                                        'font-semibold',
                                        service.isFree ? 'text-emerald-600' : ''
                                    )">
                                        {{ service.isFree
                                            ? "Gratuito"
                                            : `R$ ${service.price.toLocaleString("pt-BR", { minimumFractionDigits: 2 })}` }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Embed Code Modal -->
        <Modal :show="showEmbedModal" @close="showEmbedModal = false" maxWidth="lg">
            <div class="p-6">
                <div class="flex items-start justify-between border-b border-border pb-4 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-foreground">
                            Código de Incorporação
                        </h3>
                        <p class="text-sm text-muted-foreground mt-1">
                            Copie e cole este código no seu site para adicionar sua agenda.
                        </p>
                    </div>
                    <button 
                        @click="showEmbedModal = false"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="relative">
                    <pre class="w-full rounded-lg bg-muted p-4 text-xs text-foreground overflow-x-auto font-mono border border-border">{{ embedCode }}</pre>
                    <button
                        @click="copyEmbedCode"
                        class="absolute right-2 top-2 rounded-md bg-background border border-border p-1.5 text-foreground shadow-sm hover:bg-accent transition-colors"
                        title="Copiar código"
                    >
                        <Check v-if="copiedEmbed" class="h-4 w-4 text-emerald-500" />
                        <Copy v-else class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        @click="showEmbedModal = false"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-primary-foreground hover:bg-primary/90 transition-colors"
                    >
                        Concluído
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

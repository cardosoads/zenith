<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import NovoAgendamentoModal from '@/Components/Provider/NovoAgendamentoModal.vue';
import {
  ChevronLeft,
  ChevronRight,
  Plus,
  Clock,
  CalendarDays
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

// Usaremos apenas lógica nativa de Date para máxima compatibilidade e evitar crashes
const props = defineProps({
    rules: { type: Array, default: () => [] },
    bookings: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    selectedRange: { type: Object, default: () => ({ start: '', end: '' }) },
    selectedAgendaId: { type: [Number, String], default: null },
    today: { type: String, default: '' },
    timezone: { type: String, default: 'UTC' },
    agendas: { type: Array, default: () => [] }
});

const view = ref('week');
const currentDate = ref(new Date());
const showNewAppointment = ref(false);

const COLORS = [
  "bg-emerald-500/10 border-emerald-500/20 text-emerald-700",
  "bg-amber-500/10 border-amber-500/20 text-amber-700",
  "bg-sky-500/10 border-sky-500/20 text-sky-700",
  "bg-purple-500/10 border-purple-500/20 text-purple-700",
];

const HOURS = Array.from({ length: 15 }, (_, i) => i + 7); // 7:00 to 21:00

// Mapeamento seguro de agendamentos
const appointments = computed(() => {
    return (props.bookings || []).map((b, idx) => {
        let date;
        try {
            date = b.starts_at ? new Date(b.starts_at) : new Date();
        } catch (e) {
            date = new Date();
        }
        
        return {
            id: b.id,
            client: b.customer_name || 'Cliente Sem Nome',
            service: b.service?.name || 'Serviço',
            date: date,
            startHour: isNaN(date.getHours()) ? 0 : date.getHours(),
            startMinute: isNaN(date.getMinutes()) ? 0 : date.getMinutes(),
            duration: b.service?.duration_minutes || 60,
            color: COLORS[idx % COLORS.length]
        };
    });
});

// Utilitários de Data nativos
const formatDate = (date, options) => {
    return new Intl.DateTimeFormat('pt-BR', options).format(date);
};

const headerLabel = computed(() => {
    if (view.value === 'day') return formatDate(currentDate.value, { day: 'numeric', month: 'long', year: 'numeric' });
    
    if (view.value === 'week') {
        const start = getStartOfWeek(currentDate.value);
        const end = new Date(start);
        end.setDate(start.getDate() + 6);
        return `${formatDate(start, { day: 'numeric', month: 'short' })} - ${formatDate(end, { day: 'numeric', month: 'short', year: 'numeric' })}`;
    }
    
    return formatDate(currentDate.value, { month: 'long', year: 'numeric' });
});

function getStartOfWeek(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Segunda como início
    return new Date(d.setDate(diff));
}

const weekDays = computed(() => {
    const start = getStartOfWeek(currentDate.value);
    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        return d;
    });
});

const monthDays = computed(() => {
    const d = currentDate.value;
    const start = new Date(d.getFullYear(), d.getMonth(), 1);
    const end = new Date(d.getFullYear(), d.getMonth() + 1, 0);
    
    const calendarStart = getStartOfWeek(start);
    const days = [];
    let curr = new Date(calendarStart);
    
    // Mostra 6 semanas para garantir que o mês caiba
    for (let i = 0; i < 42; i++) {
        days.push(new Date(curr));
        curr.setDate(curr.getDate() + 1);
    }
    return days;
});

const isToday = (date) => {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
};

const isSameDay = (d1, d2) => {
    return d1.getDate() === d2.getDate() &&
           d1.getMonth() === d2.getMonth() &&
           d1.getFullYear() === d2.getFullYear();
};

const getAppointmentsForDay = (day) => {
    return appointments.value.filter((a) => isSameDay(a.date, day));
};

const goBack = () => {
    const d = new Date(currentDate.value);
    if (view.value === 'day') d.setDate(d.getDate() - 1);
    else if (view.value === 'week') d.setDate(d.getDate() - 7);
    else if (view.value === 'month') d.setMonth(d.getMonth() - 1);
    currentDate.value = d;
};

const goForward = () => {
    const d = new Date(currentDate.value);
    if (view.value === 'day') d.setDate(d.getDate() + 1);
    else if (view.value === 'week') d.setDate(d.getDate() + 7);
    else if (view.value === 'month') d.setMonth(d.getMonth() + 1);
    currentDate.value = d;
};

const goToday = () => currentDate.value = new Date();

onMounted(() => {
    console.log('✅ Carregamento completo: Agenda Premium');
});
const openNewAppointment = () => {
    console.log('Botão Novo Agendamento clicado');
    showNewAppointment.value = true;
};
</script>

<template>
    <Head title="Agenda" />

    <AuthenticatedLayout>
        <NovoAgendamentoModal 
            :open="showNewAppointment" 
            :services="services"
            :agenda-id="selectedAgendaId"
            @close="showNewAppointment = false" 
        />

        <!-- Estado: Sem Agenda Ativa -->
        <div v-if="!selectedAgendaId" class="za-card p-12 text-center rounded-[2rem] border-gray-100 bg-white shadow-xl shadow-black/[0.02]">
            <div class="mx-auto w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-6">
                <CalendarDays class="h-8 w-8 text-gray-300" />
            </div>
            <h2 class="text-2xl font-bold text-[#18181b]">Opa! Nenhuma agenda ativa.</h2>
            <p class="mt-2 text-gray-400 max-w-sm mx-auto">Para visualizar o calendário, você precisa selecionar uma agenda ativa no topo da tela.</p>
            <div class="mt-8 flex justify-center gap-3">
                <Link v-for="agenda in agendas" :key="agenda.id" :href="route('availability-rules.index', { agenda_id: agenda.id })" class="za-button za-button-neutral">
                     Selecionar {{ agenda.name }}
                </Link>
                <Link :href="route('provider.agendas.index')" class="za-button za-button-primary">Ir para Configurações</Link>
            </div>
        </div>

        <!-- Layout Premium da Agenda -->
        <div v-else class="space-y-6">
            <!-- Header Dinâmico -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#18181b]">Agenda</h1>
                    <p class="mt-1 text-sm font-bold text-gray-400 capitalize">{{ headerLabel }}</p>
                </div>
                <button 
                    @click="openNewAppointment"
                    class="flex items-center gap-2 rounded-xl bg-[#18181b] px-6 py-3 text-sm font-bold text-white transition-all hover:bg-black shadow-lg shadow-black/5 active:scale-95"
                >
                    <Plus class="h-4 w-4" />
                    Novo agendamento
                </button>
            </div>

            <!-- Controles de Visualização -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-2 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2">
                    <button @click="goBack" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:text-black hover:bg-gray-100 transition-all">
                        <ChevronLeft class="h-5 w-5" />
                    </button>
                    <button @click="goToday" class="px-5 h-10 text-xs font-bold text-[#18181b] bg-gray-50 rounded-xl hover:bg-gray-100 transition-all">Hoje</button>
                    <button @click="goForward" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:text-black hover:bg-gray-100 transition-all">
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex p-1 bg-gray-50 rounded-xl">
                    <button v-for="v in ['day', 'week', 'month']" :key="v" @click="view = v" :class="cn('px-6 py-2 text-[10px] font-extrabold uppercase tracking-widest transition-all rounded-lg', view === v ? 'bg-white text-[#18181b] shadow-sm' : 'text-gray-400 hover:text-[#18181b]')">
                        {{ v === 'day' ? 'Dia' : v === 'week' ? 'Semana' : 'Mês' }}
                    </button>
                </div>
            </div>

            <!-- Visualizações do Calendário -->
            <div class="animate-in fade-in slide-in-from-bottom-2 duration-500">
                <!-- Visão de Semana (Default Premium) -->
                <div v-if="view === 'week'" class="rounded-[2rem] border border-gray-100 bg-white shadow-xl shadow-black/[0.01] overflow-hidden overflow-x-auto no-scrollbar">
                    <div class="flex border-b border-gray-100 bg-gray-50/50">
                        <div class="w-16 shrink-0 border-r border-gray-100" />
                        <div v-for="day in weekDays" :key="day.toISOString()" :class="cn('flex-1 min-w-[150px] flex flex-col items-center py-5 border-r border-gray-100 last:border-r-0', isToday(day) && 'bg-[#18181b]/[0.02]')">
                            <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-gray-400">{{ formatDate(day, { weekday: 'short' }) }}</span>
                            <span :class="cn('mt-2 flex h-9 w-9 items-center justify-center rounded-xl text-sm font-black transition-all', isToday(day) ? 'bg-[#18181b] text-white shadow-lg shadow-black/10' : 'text-[#18181b]')">{{ day.getDate() }}</span>
                        </div>
                    </div>
                    <div class="max-h-[650px] overflow-y-auto no-scrollbar relative">
                        <div v-for="hour in HOURS" :key="hour" class="flex border-b border-gray-50 last:border-0">
                            <div class="w-16 shrink-0 flex items-start justify-end pr-4 pt-4 border-r border-gray-50 bg-gray-50/20">
                                <span class="text-[10px] font-bold text-gray-300 tabular-nums">{{ String(hour).padStart(2, '0') }}:00</span>
                            </div>
                            <div v-for="day in weekDays" :key="day.toISOString()" class="flex-1 min-w-[150px] min-h-[100px] border-r border-gray-50 last:border-r-0 p-1 relative">
                                <div v-for="apt in getAppointmentsForDay(day).filter(a => a.startHour === hour)" :key="apt.id" :class="cn('mb-1.5 rounded-xl border p-3 shadow-sm transition-all hover:scale-[1.02] cursor-pointer group', apt.color)">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-[9px] font-bold opacity-60">{{ String(apt.startHour).padStart(2, '0') }}:{{ String(apt.startMinute).padStart(2, '0') }}</span>
                                        <div class="h-1.5 w-1.5 rounded-full bg-current opacity-40"></div>
                                    </div>
                                    <p class="truncate text-[11px] font-black text-[#18181b]">{{ apt.client }}</p>
                                    <p class="truncate text-[9px] font-bold opacity-50">{{ apt.service }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visão de Dia -->
                <div v-if="view === 'day'" class="max-w-4xl mx-auto rounded-[2rem] border border-gray-100 bg-white shadow-xl shadow-black/[0.01] overflow-hidden">
                    <div class="border-b border-gray-100 px-8 py-6 bg-gray-50/50 flex items-center justify-between">
                         <span class="text-lg font-black text-[#18181b] capitalize">{{ formatDate(currentDate, { weekday: 'long', day: 'numeric', month: 'long' }) }}</span>
                         <span class="text-xs font-bold text-gray-400">{{ getAppointmentsForDay(currentDate).length }} agendamentos</span>
                    </div>
                    <div class="max-h-[600px] overflow-y-auto no-scrollbar">
                        <div v-for="hour in HOURS" :key="hour" class="flex border-b border-gray-50 group">
                            <div class="w-24 shrink-0 flex items-start justify-end pr-6 pt-5 bg-gray-50/10">
                                <span class="text-xs font-bold text-gray-300 group-hover:text-black transition-colors">{{ String(hour).padStart(2, '0') }}:00</span>
                            </div>
                            <div class="flex-1 p-3">
                                <div v-for="apt in getAppointmentsForDay(currentDate).filter(a => a.startHour === hour)" :key="apt.id" :class="cn('mb-3 rounded-2xl border p-5 shadow-sm transition-all hover:shadow-md cursor-pointer', apt.color)">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <Clock class="h-3.5 w-3.5 opacity-40" />
                                            <span class="text-xs font-black">{{ String(apt.startHour).padStart(2, '0') }}:{{ String(apt.startMinute).padStart(2, '0') }} — {{ apt.duration }}min</span>
                                        </div>
                                    </div>
                                    <h4 class="text-base font-black text-[#18181b]">{{ apt.client }}</h4>
                                    <p class="text-xs font-bold opacity-60 mt-1">{{ apt.service }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visão de Mês -->
                <div v-if="view === 'month'" class="rounded-[2.5rem] border border-gray-100 bg-white shadow-xl shadow-black/[0.01] overflow-hidden">
                    <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/50">
                        <div v-for="label in ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab', 'Dom']" :key="label" class="py-5 text-center text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
                            {{ label }}
                        </div>
                    </div>
                    <div class="grid grid-cols-7">
                        <button v-for="(day, idx) in monthDays" :key="idx" @click="currentDate = day; view = 'day'" :class="cn('min-h-[130px] p-4 text-left border-b border-r border-gray-50 transition-all hover:bg-gray-50 group', day.getMonth() !== currentDate.getMonth() && 'opacity-20')">
                            <span :class="cn('inline-flex h-8 w-8 items-center justify-center rounded-xl text-xs font-black transition-all group-hover:scale-110', isToday(day) ? 'bg-[#18181b] text-white shadow-lg' : 'text-gray-400 group-hover:text-black')">{{ day.getDate() }}</span>
                            <div class="mt-3 flex flex-col gap-1.5">
                                <div v-for="apt in getAppointmentsForDay(day).slice(0, 2)" :key="apt.id" :class="cn('truncate rounded-lg px-2 py-1 text-[9px] font-black shadow-sm', apt.color)">
                                    {{ apt.client }}
                                </div>
                                <span v-if="getAppointmentsForDay(day).length > 2" class="text-[8px] font-black text-gray-300 uppercase pl-1">
                                    +{{ getAppointmentsForDay(day).length - 2 }} mais
                                </span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

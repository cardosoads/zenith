<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VendaDetailSheet from '@/Components/Provider/VendaDetailSheet.vue';
import { 
    Plus, 
    Search, 
    Filter, 
    Download, 
    DollarSign, 
    TrendingUp, 
    ShoppingCart, 
    ArrowUpRight, 
    MoreHorizontal, 
    CalendarDays, 
    MessageSquare, 
    Paperclip, 
    List, 
    LayoutGrid, 
    Table2, 
    ArrowDownUp, 
    GripVertical,
    X,
    Clock,
    User,
    Eye
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    bookings: { type: Array, default: () => [] },
    selectedAgendaId: [Number, String],
    agendas: { type: Array, default: () => [] },
});

const viewMode = ref('kanban'); // 'list' | 'kanban' | 'table'
const searchQuery = ref('');
const showDetail = ref(false);
const detailCard = ref(null);

// Tag colors from agendamentos.md
const tagStyles = {
    yellow: "bg-amber-100 text-amber-700",
    blue: "bg-sky-100 text-sky-700",
    red: "bg-rose-100 text-rose-700",
    green: "bg-emerald-100 text-emerald-700",
    purple: "bg-violet-100 text-violet-700",
    orange: "bg-orange-100 text-orange-700",
};

// Column config from agendamentos.md
const dotColors = {
    novo: "bg-amber-400",
    agendamento_confirmado: "bg-sky-400",
    fechado: "bg-emerald-400",
    perdido: "bg-rose-400",
};

const columnTitles = {
    novo: "Novo",
    agendamento_confirmado: "Agendamento confirmado",
    fechado: "Fechado",
    perdido: "Perdido",
};

// Map real statuses to the design's columns
const statusMapping = {
    pending: 'novo',
    confirmed: 'agendamento_confirmado',
    completed: 'fechado',
    cancelled: 'perdido',
};

// KPIs following the exact labels and logic
const totalReceita = computed(() => 
    props.bookings.filter(b => b.status === 'completed').reduce((acc, b) => acc + (b.service?.price_cents || 0), 0) / 100
);

const totalPendente = computed(() => 
    props.bookings.filter(b => b.status === 'pending' || b.status === 'confirmed').reduce((acc, b) => acc + (b.service?.price_cents || 0), 0) / 100
);

const totalVendas = computed(() => props.bookings.filter(b => b.status === 'completed').length);

const totalPerdido = computed(() => 
    props.bookings.filter(b => b.status === 'cancelled').reduce((acc, b) => acc + (b.service?.price_cents || 0), 0) / 100
);

const totalPerdidoCount = computed(() => props.bookings.filter(b => b.status === 'cancelled').length);

// Kanban Columns
const columns = computed(() => {
    const ids = ['novo', 'agendamento_confirmado', 'fechado', 'perdido'];
    return ids.map(id => ({
        id,
        title: columnTitles[id],
        dotColor: dotColors[id],
        cards: filteredBookings.value.filter(b => statusMapping[b.status] === id)
    }));
});

const filteredBookings = computed(() => {
    if (!searchQuery.value) return props.bookings;
    const q = searchQuery.value.toLowerCase();
    return props.bookings.filter(b => 
        b.customer_name?.toLowerCase().includes(q) || 
        b.service?.name?.toLowerCase().includes(q)
    );
});

const draggedBookingId = ref(null);

const handleDragStart = (e, bookingId) => {
    draggedBookingId.value = bookingId;
    e.dataTransfer.effectAllowed = 'move';
};

const handleDragOver = (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
};

const handleDrop = (e, columnId) => {
    e.preventDefault();
    if (!draggedBookingId.value) return;

    const targetStatus = Object.keys(statusMapping).find(key => statusMapping[key] === columnId);
    
    if (targetStatus) {
        useForm({ status: targetStatus }).patch(route('bookings.status', draggedBookingId.value), {
            preserveScroll: true,
            onSuccess: () => {
                draggedBookingId.value = null;
            }
        });
    }
};

const handleViewDetail = (booking) => {
    detailCard.value = booking;
    showDetail.value = true;
};

const formatCurrency = (val) => {
    return val.toLocaleString('pt-BR');
};

const formatDate = (dateStr) => {
    return new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(dateStr));
};
</script>

<template>
    <Head title="Vendas" />

    <AuthenticatedLayout>
        <VendaDetailSheet
            :open="showDetail"
            :booking="detailCard"
            @close="showDetail = false"
        />

        <div v-if="!selectedAgendaId" class="flex flex-col items-center justify-center py-20 bg-white border border-dashed border-gray-200 rounded-lg">
            <CalendarDays class="h-10 w-10 text-gray-300 mb-4" />
            <h2 class="text-xl font-semibold text-gray-900">Selecione uma agenda</h2>
            <p class="text-sm text-gray-500 mt-1 mb-6">Para gerenciar as vendas, selecione uma das suas agendas.</p>
            <div class="flex gap-2">
                <Link v-for="agenda in agendas" :key="agenda.id" :href="route('bookings.index', { agenda_id: agenda.id })" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    {{ agenda.name }}
                </Link>
            </div>
        </div>

        <div v-else>
            <!-- Page Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900">
                        Vendas
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Gerencie todas as vendas e agendamentos.
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start">
                    <button class="flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-slate-900 transition-colors hover:bg-gray-50">
                        <Download class="h-4 w-4" />
                        Exportar
                    </button>
                    <Link :href="route('availability-rules.index')" class="flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                        <Plus class="h-4 w-4" />
                        Nova venda
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Receita fechada
                        </span>
                        <DollarSign class="h-4 w-4 text-gray-400" />
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-semibold tracking-tight text-slate-900">
                            R$ {{ formatCurrency(totalReceita) }}
                        </span>
                        <span class="mb-0.5 flex items-center gap-0.5 text-xs font-medium text-emerald-600">
                            <ArrowUpRight class="h-3 w-3" />
                            +12%
                        </span>
                    </div>
                </div>
                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Em andamento
                        </span>
                        <TrendingUp class="h-4 w-4 text-gray-400" />
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-semibold tracking-tight text-slate-900">
                            R$ {{ formatCurrency(totalPendente) }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Vendas fechadas
                        </span>
                        <ShoppingCart class="h-4 w-4 text-gray-400" />
                    </div>
                    <span class="text-2xl font-semibold tracking-tight text-slate-900">
                        {{ totalVendas }}
                    </span>
                </div>
                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500">
                            Perdido
                        </span>
                        <X class="h-4 w-4 text-gray-400" />
                    </div>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-semibold tracking-tight text-slate-900">
                            R$ {{ formatCurrency(totalPerdido) }}
                        </span>
                        <span class="mb-0.5 text-xs text-gray-500">
                            {{ totalPerdidoCount }} {{ totalPerdidoCount === 1 ? "venda" : "vendas" }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- View Tabs + Actions -->
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center">
                    <div class="flex items-center rounded-lg border border-gray-200 bg-white p-1">
                        <button
                            v-for="mode in ['list', 'kanban', 'table']" :key="mode"
                            @click="viewMode = mode"
                            :class="cn(
                                'flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors',
                                viewMode === mode
                                    ? 'bg-gray-100 text-slate-900'
                                    : 'text-gray-500 hover:text-slate-900'
                            )"
                        >
                            <component :is="mode === 'list' ? List : mode === 'kanban' ? LayoutGrid : Table2" class="h-3.5 w-3.5" />
                            {{ mode === 'list' ? 'Lista' : mode === 'kanban' ? 'Kanban' : 'Tabela' }}
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar..."
                            class="h-8 w-48 rounded-md border border-gray-200 bg-white pl-8 pr-3 text-xs text-slate-900 placeholder:text-gray-400 focus:border-slate-900 focus:outline-none focus:ring-0"
                        />
                        <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-slate-900">
                            <X class="h-3 w-3" />
                        </button>
                    </div>
                    <button class="flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-slate-900">
                        <ArrowDownUp class="h-3.5 w-3.5" />
                        Ordenar
                    </button>
                    <button class="flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-500 transition-colors hover:bg-gray-50 hover:text-slate-900">
                        <Filter class="h-3.5 w-3.5" />
                        Filtrar
                    </button>
                </div>
            </div>

            <!-- Kanban Board -->
            <div v-if="viewMode === 'kanban'" class="flex gap-5 overflow-x-auto pb-4 no-scrollbar">
                <div 
                    v-for="col in columns" :key="col.id" 
                    class="flex min-w-[320px] flex-1 flex-col"
                    @dragover="handleDragOver"
                    @drop="handleDrop($event, col.id)"
                >
                    <!-- Column Header -->
                    <div class="mb-4 flex items-center gap-2.5 px-1">
                        <div :class="cn('h-2.5 w-2.5 rounded-full', col.dotColor)" />
                        <h3 class="text-sm font-semibold text-slate-900">{{ col.title }}</h3>
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1.5 text-[10px] font-medium tabular-nums text-gray-500">
                            {{ col.cards.length }}
                        </span>
                    </div>

                    <!-- Create task button (as link) -->
                    <Link :href="route('availability-rules.index')" class="mb-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-gray-200 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:border-gray-400 hover:bg-gray-50 hover:text-slate-900">
                        <Plus class="h-4 w-4" />
                        Criar tarefa
                    </Link>

                    <!-- Cards Area -->
                    <div class="flex flex-1 flex-col gap-3 p-1 min-h-[150px]">
                        <div 
                            v-for="card in col.cards" :key="card.id"
                            draggable="true"
                            @dragstart="handleDragStart($event, card.id)"
                            @click="handleViewDetail(card)"
                            class="group cursor-grab active:cursor-grabbing rounded-lg border border-gray-200 bg-white p-4 transition-all hover:shadow-sm"
                        >
                            <div class="mb-3 flex items-start justify-between gap-2">
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="rounded px-2 py-0.5 text-[11px] font-medium leading-tight bg-sky-100 text-sky-700">
                                        {{ card.service?.name }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                    <button class="flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:bg-gray-100 hover:text-slate-900">
                                        <Eye class="h-3.5 w-3.5" />
                                    </button>
                                    <button class="flex h-6 w-6 items-center justify-center rounded text-gray-400 hover:bg-gray-100">
                                        <MoreHorizontal class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </div>

                            <h4 class="mb-3 text-sm font-medium leading-snug text-slate-900">
                                {{ card.customer_name }}
                            </h4>

                            <div class="mb-3 flex items-center gap-1.5">
                                <User class="h-3 w-3 text-gray-400" />
                                <span class="text-xs text-gray-500">{{ card.customer_name }}</span>
                                <span class="ml-auto text-xs font-medium tabular-nums text-slate-900">
                                    R$ {{ formatCurrency(card.service?.price_cents / 100) }}
                                </span>
                            </div>

                            <div class="mb-3 flex items-center gap-3">
                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <CalendarDays class="h-3 w-3" />
                                    <span>{{ formatDate(card.starts_at) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <Clock class="h-3 w-3" />
                                    <span class="tabular-nums">{{ new Date(card.starts_at).getHours() }}:{{ String(new Date(card.starts_at).getMinutes()).padStart(2, '0') }}</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex -space-x-1.5">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-gray-100 text-[9px] font-medium text-gray-500">
                                        {{ card.customer_name?.charAt(0) }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1 text-xs text-gray-500">
                                        <MessageSquare class="h-3 w-3" />
                                        <span class="tabular-nums">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="col.cards.length === 0" class="flex flex-col items-center justify-center rounded-lg border border-dashed border-gray-200 py-10">
                            <p class="text-xs text-gray-400">Arraste cards para cá</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-if="viewMode === 'list'" class="flex flex-col gap-3">
                <div v-for="col in columns" :key="col.id">
                    <div class="mb-3 flex items-center gap-2.5">
                        <div :class="cn('h-2.5 w-2.5 rounded-full', col.dotColor)" />
                        <h3 class="text-sm font-semibold text-slate-900">{{ col.title }}</h3>
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1.5 text-[10px] font-medium tabular-nums text-gray-500">
                            {{ col.cards.length }}
                        </span>
                    </div>
                    <div class="mb-5 flex flex-col gap-2">
                        <div v-for="card in col.cards" :key="card.id" @click="handleViewDetail(card)" class="flex items-center gap-4 rounded-lg border border-gray-200 bg-white px-4 py-3 transition-colors hover:bg-gray-50 cursor-pointer">
                            <GripVertical class="h-4 w-4 shrink-0 text-gray-300" />
                            <div class="flex flex-1 flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-4">
                                <div class="flex flex-1 flex-col gap-1">
                                    <span class="text-sm font-medium text-slate-900">{{ card.customer_name }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">{{ card.service?.name }}</span>
                                        <span class="text-xs text-gray-500">—</span>
                                        <span class="text-xs font-medium tabular-nums text-slate-900">R$ {{ formatCurrency(card.service?.price_cents / 100) }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="rounded px-2 py-0.5 text-[10px] font-medium bg-sky-100 text-sky-700">
                                        Marketing
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <CalendarDays class="h-3 w-3" />
                                        {{ formatDate(card.starts_at) }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <MessageSquare class="h-3 w-3" />
                                        0
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button class="flex h-7 w-7 items-center justify-center rounded text-gray-400 hover:bg-white hover:text-slate-900 shadow-sm border border-transparent hover:border-gray-200">
                                    <Eye class="h-4 w-4" />
                                </button>
                                <button class="flex h-7 w-7 items-center justify-center rounded text-gray-400 hover:bg-white">
                                    <MoreHorizontal class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <div v-if="col.cards.length === 0" class="rounded-lg border border-dashed border-gray-200 py-6 text-center text-xs text-gray-400">
                            Nenhuma tarefa nesta etapa.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table View -->
            <div v-if="viewMode === 'table'" class="rounded-lg border border-gray-200 bg-white overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Título/Cliente</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Serviço</th>
                                <th class="hidden px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:table-cell">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Valor</th>
                                <th class="hidden px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 lg:table-cell">Data</th>
                                <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="booking in filteredBookings" :key="booking.id" class="transition-colors hover:bg-gray-50 group">
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-slate-900">
                                    {{ booking.customer_name }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-500">
                                    {{ booking.service?.name }}
                                </td>
                                <td class="hidden whitespace-nowrap px-5 py-3.5 sm:table-cell">
                                    <div class="flex items-center gap-1.5">
                                        <div :class="cn('h-2 w-2 rounded-full', dotColors[statusMapping[booking.status]])" />
                                        <span class="text-xs text-gray-500">{{ columnTitles[statusMapping[booking.status]] }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium tabular-nums text-slate-900">
                                    R$ {{ formatCurrency(booking.service?.price_cents / 100) }}
                                </td>
                                <td class="hidden whitespace-nowrap px-5 py-3.5 text-xs text-gray-500 lg:table-cell">
                                    {{ formatDate(booking.starts_at) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="handleViewDetail(booking)" class="flex h-7 w-7 items-center justify-center rounded text-gray-400 hover:bg-white hover:text-slate-900 border border-transparent hover:border-gray-200">
                                            <Eye class="h-4 w-4" />
                                        </button>
                                        <Link :href="route('bookings.show', booking.id)" class="flex h-7 w-7 items-center justify-center rounded text-gray-400 hover:bg-white">
                                            <ArrowUpRight class="h-4 w-4" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="filteredBookings.length === 0" class="flex flex-col items-center justify-center py-12">
                    <p class="text-sm text-gray-500">Nenhuma tarefa encontrada.</p>
                </div>
                <div class="flex items-center justify-between border-t border-gray-200 px-5 py-3 bg-gray-50/30">
                    <span class="text-xs text-gray-500">{{ filteredBookings.length }} tarefas</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

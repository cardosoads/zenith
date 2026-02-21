<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import {
    DollarSign,
    TrendingUp,
    Users,
    CalendarDays,
    Download,
    ArrowUpRight,
    ArrowDownRight,
    Search,
    ChevronDown,
    Filter
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    kpis: Object,
    revenueByMonth: Array,
    appointmentsPerDay: Array,
    serviceBreakdown: Array,
    topServices: Array,
    agendas: Array,
    selectedAgendaId: [Number, null],
});

const period = ref('30d');
const periods = [
    { key: '7d', label: '7 dias' },
    { key: '30d', label: '30 dias' },
    { key: '90d', label: '90 dias' },
    { key: '12m', label: '12 meses' },
];

const formatCurrency = (val) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

/* -------- Mini bar chart helpers -------- */
const revenueMax = computed(() =>
    Math.max(...(props.revenueByMonth || []).map((d) => d.value), 1)
);
const appointmentsMax = computed(() =>
    Math.max(...(props.appointmentsPerDay || []).map((d) => d.value), 1)
);

/* -------- Pie chart SVG helper -------- */
const pieSlices = computed(() => {
    const data = props.serviceBreakdown || [];
    const total = data.reduce((acc, d) => acc + d.value, 0) || 1;
    const slices = [];
    let cumulative = 0;

    data.forEach((d) => {
        const startAngle = (cumulative / total) * 360;
        const sliceAngle = (d.value / total) * 360;
        cumulative += d.value;

        const startRad = ((startAngle - 90) * Math.PI) / 180;
        const endRad = (((startAngle + sliceAngle) - 90) * Math.PI) / 180;

        const outerR = 80;
        const innerR = 50;
        const largeArc = sliceAngle > 180 ? 1 : 0;

        const x1Outer = 100 + outerR * Math.cos(startRad);
        const y1Outer = 100 + outerR * Math.sin(startRad);
        const x2Outer = 100 + outerR * Math.cos(endRad);
        const y2Outer = 100 + outerR * Math.sin(endRad);
        const x1Inner = 100 + innerR * Math.cos(endRad);
        const y1Inner = 100 + innerR * Math.sin(endRad);
        const x2Inner = 100 + innerR * Math.cos(startRad);
        const y2Inner = 100 + innerR * Math.sin(startRad);

        const path = [
            `M ${x1Outer} ${y1Outer}`,
            `A ${outerR} ${outerR} 0 ${largeArc} 1 ${x2Outer} ${y2Outer}`,
            `L ${x1Inner} ${y1Inner}`,
            `A ${innerR} ${innerR} 0 ${largeArc} 0 ${x2Inner} ${y2Inner}`,
            'Z',
        ].join(' ');

        slices.push({ path, color: d.color, name: d.name, value: d.value });
    });
    return slices;
});
</script>

<template>
    <Head title="Relatórios" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Relatórios
                </h1>
                <p class="mt-1 text-muted-foreground">
                    Análise detalhada do desempenho do seu negócio.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-semibold text-foreground shadow-sm transition-all hover:bg-accent active:scale-95">
                    <Download class="h-4 w-4" />
                    Exportar PDF
                </button>
            </div>
        </div>

        <!-- Filters Row -->
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-border bg-card p-4 shadow-sm">
            <div class="flex items-center gap-1 p-1 bg-muted rounded-lg w-fit">
                <button
                    v-for="p in periods"
                    :key="p.key"
                    @click="period = p.key"
                    :class="cn(
                        'px-4 py-1.5 text-xs font-semibold rounded-md transition-all',
                        period === p.key
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'
                    )"
                >
                    {{ p.label }}
                </button>
            </div>

            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-1.5 text-xs font-medium bg-card hover:bg-accent text-foreground transition-all">
                    <Filter class="h-3.5 w-3.5" />
                    Mais Filtros
                </button>
            </div>
        </div>

        <!-- KPIs Grid -->
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Receita"
                :value="formatCurrency(kpis.revenue)"
                change="+18%"
                changeType="positive"
                :icon="DollarSign"
            />
            <StatCard
                title="Agendamentos"
                :value="kpis.appointments"
                change="+8%"
                changeType="positive"
                :icon="CalendarDays"
            />
            <StatCard
                title="Novos Clientes"
                :value="kpis.newCustomers"
                change="+5%"
                changeType="positive"
                :icon="Users"
            />
            <StatCard
                title="Cancelamentos"
                :value="kpis.cancellationRate + '%'"
                change="-2%"
                changeType="negative"
                :icon="TrendingUp"
            />
        </div>

        <!-- Charts Grid -->
        <div class="mb-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Revenue Bar Chart Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="border-b border-border px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-foreground">Receita Mensal</h2>
                        <p class="text-xs text-muted-foreground">Projeção dos últimos 12 meses</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex items-end justify-between gap-2 h-64">
                        <div
                            v-for="(bar, idx) in revenueByMonth"
                            :key="idx"
                            class="flex flex-1 flex-col items-center justify-end h-full gap-2 group"
                        >
                            <div class="relative w-full flex items-end justify-center h-full">
                                <div
                                    class="w-full max-w-[40px] rounded-t-lg transition-all duration-300 bg-primary hover:opacity-80 cursor-pointer"
                                    :style="{ height: (bar.value / revenueMax) * 100 + '%', minHeight: bar.value > 0 ? '4px' : '0' }"
                                />
                                <!-- Tooltip placeholder -->
                                <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                    <div class="bg-foreground text-background text-[10px] px-2 py-1 rounded shadow-lg whitespace-nowrap">
                                        {{ formatCurrency(bar.value) }}
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-muted-foreground font-medium uppercase tracking-tighter">{{ bar.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Line Chart Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="border-b border-border px-6 py-4">
                    <h2 class="text-sm font-semibold text-foreground">Agendamentos por Dia</h2>
                    <p class="text-xs text-muted-foreground">Média de volume semanal</p>
                </div>
                <div class="p-6">
                    <div class="flex items-end justify-between gap-2 h-64">
                        <div
                            v-for="(day, idx) in appointmentsPerDay"
                            :key="idx"
                            class="flex flex-1 flex-col items-center justify-end h-full gap-2 group"
                        >
                            <div class="relative w-full flex items-end justify-center h-full">
                                <div
                                    class="w-full max-w-[40px] rounded-t-lg transition-all duration-300 bg-emerald-500 hover:bg-emerald-400 cursor-pointer"
                                    :style="{ height: (day.value / appointmentsMax) * 100 + '%', minHeight: day.value > 0 ? '4px' : '0' }"
                                />
                                <div class="absolute bottom-full mb-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                                    <div class="bg-foreground text-background text-[10px] px-2 py-1 rounded shadow-lg whitespace-nowrap">
                                        {{ day.value }} agendamentos
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-muted-foreground font-medium uppercase tracking-tighter">{{ day.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Insights Row -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Services Distribution (Donut Chart) -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="border-b border-border px-6 py-4">
                    <h2 class="text-sm font-semibold text-foreground">Serviços Favoritos</h2>
                </div>
                <div class="p-8">
                    <div class="flex items-center justify-center mb-8 relative">
                        <svg viewBox="0 0 200 200" class="w-48 h-48 drop-shadow-sm">
                            <path
                                v-for="(slice, idx) in pieSlices"
                                :key="idx"
                                :d="slice.path"
                                :fill="slice.color"
                                class="transition-all duration-300 hover:scale-105 cursor-pointer origin-center"
                            />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-bold text-foreground">100%</span>
                            <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Total</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="item in serviceBreakdown" :key="item.name" class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full" :style="{ backgroundColor: item.color }" />
                            <span class="text-[10px] font-medium text-muted-foreground truncate">{{ item.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Professionals Card (Placeholders) -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="border-b border-border px-6 py-4">
                    <h2 class="text-sm font-semibold text-foreground">Top Profissionais</h2>
                </div>
                <div class="divide-y divide-border">
                    <div v-for="(pro, i) in [
                        { name: 'Ana Costa', revenue: 6800, appointments: 48 },
                        { name: 'Pedro Oliveira', revenue: 5200, appointments: 52 },
                        { name: 'Carla Santos', revenue: 4100, appointments: 38 },
                    ]" :key="pro.name" class="flex items-center gap-4 px-6 py-4 transition-colors hover:bg-accent/50">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                            {{ i + 1 }}
                        </span>
                        <div class="flex flex-1 flex-col">
                            <span class="text-sm font-semibold text-foreground">{{ pro.name }}</span>
                            <span class="text-xs text-muted-foreground">{{ pro.appointments }} atendimentos</span>
                        </div>
                        <span class="text-sm font-bold tabular-nums text-foreground">
                            {{ formatCurrency(pro.revenue) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Top Services Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="border-b border-border px-6 py-4">
                    <h2 class="text-sm font-semibold text-foreground">Top Serviços</h2>
                </div>
                <div class="divide-y divide-border">
                    <div v-for="service in topServices" :key="service.name" class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-accent/50">
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold text-foreground">{{ service.name }}</span>
                            <span class="text-xs text-muted-foreground">{{ service.quantity }} agendamentos</span>
                        </div>
                        <span class="text-sm font-bold tabular-nums text-foreground">
                            {{ formatCurrency(service.revenue) }}
                        </span>
                    </div>
                    <div v-if="!topServices?.length" class="flex flex-col items-center justify-center py-12 text-center">
                        <p class="text-xs text-muted-foreground">Nenhum dado disponível.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


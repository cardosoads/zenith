<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { TrendingUp, TrendingDown, Users, DollarSign, BarChart2, Repeat, Activity, Calendar, ShieldUser, ArrowUpRight } from 'lucide-vue-next';

const props = defineProps({
    metrics: Object,
});

// ── Formatters ────────────────────────────────────────────────────────
const fmtBRL = (cents) => {
    const v = (cents ?? 0) / 100;
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 0 }).format(v);
};

const fmtNum = (n) => new Intl.NumberFormat('pt-BR').format(n ?? 0);
const fmtPct = (n) => `${(n ?? 0).toFixed(1)}%`;

// ── KPI Cards ────────────────────────────────────────────────────────
const kpis = computed(() => [
    {
        id: 'mrr',
        label: 'MRR',
        value: fmtBRL(props.metrics.mrr_cents),
        sub: 'Receita Recorrente Mensal',
        icon: DollarSign,
        trend: props.metrics.mom_growth_pct,
    },
    {
        id: 'subscribers',
        label: 'Assinantes Ativos',
        value: fmtNum(props.metrics.total_active_subscribers),
        sub: `${fmtNum(props.metrics.active_providers)} prestadores ativos`,
        icon: Users,
        trend: props.metrics.mom_growth_pct,
    },
    {
        id: 'churn',
        label: 'Churn Rate',
        value: fmtPct(props.metrics.churn_rate_pct),
        sub: 'Cancelamentos no mês anterior',
        icon: Repeat,
        trend: null,
        invertTrend: true,
    },
    {
        id: 'growth',
        label: 'Crescimento MoM',
        value: fmtPct(props.metrics.mom_growth_pct),
        sub: 'Variação de assinantes (mês a mês)',
        icon: TrendingUp,
        trend: props.metrics.mom_growth_pct,
    },
    {
        id: 'arpu',
        label: 'ARPU',
        value: fmtBRL(props.metrics.arpu_cents),
        sub: 'Receita média por assinante',
        icon: BarChart2,
        trend: null,
    },
    {
        id: 'ltv',
        label: 'LTV Estimado',
        value: fmtBRL(props.metrics.ltv_cents),
        sub: 'Lifetime Value (ARPU / Churn)',
        icon: Activity,
        trend: null,
    },
]);

// ── Plan / segment neutral palette ──────────────────────────────────
// Softer, less saturated progression
const barPalette = ['bg-foreground/70', 'bg-foreground/40', 'bg-foreground/20', 'bg-muted-foreground/50', 'bg-muted-foreground/30'];

// ── SVG Area Chart (MRR) ──────────────────────────────────────────────
const mrrChart = computed(() => {
    const series = props.metrics.monthly_series ?? [];
    if (!series.length) return null;

    const W = 700, H = 160, PAD = 10;
    const vals = series.map((s) => s.mrr_cents);
    const maxV = Math.max(...vals, 1);
    const minV = 0;

    const pts = series.map((s, i) => {
        const x = PAD + (i / (series.length - 1)) * (W - PAD * 2);
        const y = H - PAD - ((s.mrr_cents - minV) / (maxV - minV)) * (H - PAD * 2);
        return { x, y, label: s.label, val: s.mrr_cents };
    });

    const pathD = pts.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
    const areaD = `${pathD} L ${pts[pts.length - 1].x} ${H - PAD} L ${pts[0].x} ${H - PAD} Z`;

    return { W, H, pts, pathD, areaD };
});

// ── SVG Bar Chart (New vs Cancelled) ──────────────────────────────────
const barChart = computed(() => {
    const series = props.metrics.monthly_series ?? [];
    if (!series.length) return null;

    const W = 700, H = 160, PAD = 10;
    const maxVal = Math.max(...series.map((s) => Math.max(s.new_subscribers, s.cancelled_subscribers)), 1);
    const barW = ((W - PAD * 2) / series.length) * 0.35;
    const gap = ((W - PAD * 2) / series.length) * 0.1;

    const bars = series.map((s, i) => {
        const slotW = (W - PAD * 2) / series.length;
        const slotX = PAD + i * slotW;
        const newH = (s.new_subscribers / maxVal) * (H - PAD * 2);
        const cancH = (s.cancelled_subscribers / maxVal) * (H - PAD * 2);
        return {
            label: s.label,
            newX: slotX + gap,
            newH,
            newY: H - PAD - newH,
            newVal: s.new_subscribers,
            cancX: slotX + gap + barW + gap,
            cancH,
            cancY: H - PAD - cancH,
            cancVal: s.cancelled_subscribers,
            barW,
            cx: slotX + slotW / 2,
        };
    });

    return { W, H, bars };
});

// ── Bookings chart (sparkline) ─────────────────────────────────────────
const bookingsChart = computed(() => {
    const series = props.metrics.monthly_series ?? [];
    if (!series.length) return null;

    const W = 700, H = 100, PAD = 8;
    const vals = series.map((s) => s.bookings);
    const maxV = Math.max(...vals, 1);

    const pts = series.map((s, i) => {
        const x = PAD + (i / (series.length - 1)) * (W - PAD * 2);
        const y = H - PAD - (s.bookings / maxV) * (H - PAD * 2);
        return { x, y, label: s.label, val: s.bookings };
    });

    const pathD = pts.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
    const areaD = `${pathD} L ${pts[pts.length - 1].x} ${H - PAD} L ${pts[0].x} ${H - PAD} Z`;

    return { W, H, pts, pathD, areaD };
});

// ── Revenue by plan (bar widths) ────────────────────────────────────────
const planRevenue = computed(() => {
    const plans = props.metrics.revenue_by_plan ?? [];
    const maxRev = Math.max(...plans.map((p) => p.revenue_cents), 1);
    return plans.map((p) => ({
        ...p,
        pct: Math.round((p.revenue_cents / maxRev) * 100),
    }));
});

// ── Revenue by segment (bar widths) ──────────────────────────────────────
const segmentRevenue = computed(() => {
    const segs = props.metrics.revenue_by_segment ?? [];
    const maxRev = Math.max(...segs.map((s) => s.revenue_cents), 1);
    return segs.map((s) => ({
        ...s,
        pct: Math.round((s.revenue_cents / maxRev) * 100),
    }));
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <section class="space-y-8">

            <!-- Header -->
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Painel Administrativo</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight">Visão Financeira & Crescimento</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Métricas SaaS em tempo real da plataforma Zenith</p>
                </div>
                <Link
                    :href="route('admin.providers.index')"
                    class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-colors"
                >
                    <ShieldUser class="h-4 w-4" />
                    Gerenciar Prestadores
                    <ArrowUpRight class="h-3.5 w-3.5 opacity-70" />
                </Link>
            </div>

            <!-- ── Linha 1: KPI Cards ── -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                <article
                    v-for="kpi in kpis"
                    :key="kpi.id"
                    class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <!-- Icon -->
                    <div class="mb-3 flex items-start justify-between">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-muted">
                            <component :is="kpi.icon" class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <!-- Trend badge -->
                        <span
                            v-if="kpi.trend !== null && kpi.trend !== undefined"
                            class="inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-semibold"
                            :class="kpi.trend >= 0 ? 'bg-muted text-foreground' : 'bg-muted text-muted-foreground'"
                        >
                            <TrendingUp v-if="kpi.trend >= 0" class="h-3 w-3" />
                            <TrendingDown v-else class="h-3 w-3" />
                            {{ Math.abs(kpi.trend).toFixed(1) }}%
                        </span>
                    </div>
                    <!-- Value -->
                    <p class="text-2xl font-bold tracking-tight text-foreground">{{ kpi.value }}</p>
                    <p class="mt-0.5 text-xs font-semibold text-foreground">{{ kpi.label }}</p>
                    <p class="mt-1 text-[11px] leading-snug text-muted-foreground">{{ kpi.sub }}</p>
                </article>
            </div>

            <!-- ── Linha 2: Gráfico MRR ── -->
            <div class="rounded-2xl border border-border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">Crescimento de Receita (MRR)</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Últimos 6 meses</p>
                    </div>
                    <span class="rounded-full bg-muted px-3 py-1 text-xs font-semibold text-foreground">
                        {{ fmtBRL(metrics.mrr_cents) }} atual
                    </span>
                </div>

                <div v-if="mrrChart" class="relative">
                    <svg
                        :viewBox="`0 0 ${mrrChart.W} ${mrrChart.H}`"
                        preserveAspectRatio="none"
                        class="h-44 w-full"
                    >
                        <defs>
                            <linearGradient id="mrrGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="currentColor" stop-opacity="0.15" />
                                <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <!-- Grid lines -->
                        <line v-for="i in 4" :key="i"
                            :x1="10" :x2="mrrChart.W - 10"
                            :y1="10 + ((i - 1) / 3) * (mrrChart.H - 20)"
                            :y2="10 + ((i - 1) / 3) * (mrrChart.H - 20)"
                            stroke="currentColor" stroke-opacity="0.06" stroke-width="1"
                        />
                        <!-- Area fill -->
                        <path :d="mrrChart.areaD" fill="url(#mrrGrad)" class="text-foreground" />
                        <!-- Line -->
                        <path :d="mrrChart.pathD" fill="none" stroke="currentColor" stroke-width="2" stroke-opacity="0.6" stroke-linecap="round" stroke-linejoin="round" />
                        <!-- Dots -->
                        <g v-for="(pt, i) in mrrChart.pts" :key="i">
                            <circle :cx="pt.x" :cy="pt.y" r="3.5" fill="currentColor" fill-opacity="0.8" />
                        </g>
                    </svg>
                    <!-- X labels -->
                    <div class="mt-2 flex justify-between px-2">
                        <span v-for="(s, i) in metrics.monthly_series" :key="i" class="text-[11px] text-muted-foreground">{{ s.label }}</span>
                    </div>
                    <!-- Tooltip values -->
                    <div class="mt-3 flex justify-between px-2">
                        <span v-for="(s, i) in metrics.monthly_series" :key="i" class="text-[11px] font-semibold text-foreground/70">
                            {{ fmtBRL(s.mrr_cents) }}
                        </span>
                    </div>
                </div>
                <div v-else class="flex h-44 items-center justify-center text-sm text-muted-foreground">
                    Sem dados suficientes para exibir o gráfico
                </div>
            </div>

            <!-- ── Linha 3: Novos vs Cancelamentos ── -->
            <div class="rounded-2xl border border-border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold">Novos vs Cancelamentos</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Evolução de assinantes por mês</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-foreground/70"></span>
                            Novos
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-foreground/25"></span>
                            Cancelamentos
                        </span>
                    </div>
                </div>

                <div v-if="barChart">
                    <svg :viewBox="`0 0 ${barChart.W} ${barChart.H}`" preserveAspectRatio="none" class="h-40 w-full">
                        <!-- Grid -->
                        <line v-for="i in 4" :key="i"
                            :x1="10" :x2="barChart.W - 10"
                            :y1="10 + ((i - 1) / 3) * (barChart.H - 20)"
                            :y2="10 + ((i - 1) / 3) * (barChart.H - 20)"
                            stroke="currentColor" stroke-opacity="0.06" stroke-width="1"
                        />
                        <g v-for="b in barChart.bars" :key="b.label">
                            <!-- New subscribers bar -->
                            <rect
                                :x="b.newX" :y="b.newY" :width="b.barW" :height="Math.max(b.newH, 2)"
                                rx="3" fill="currentColor" fill-opacity="0.7"
                            />
                            <!-- Cancelled bar -->
                            <rect
                                :x="b.cancX" :y="b.cancY" :width="b.barW" :height="Math.max(b.cancH, 2)"
                                rx="3" fill="currentColor" fill-opacity="0.25"
                            />
                        </g>
                    </svg>
                    <div class="mt-2 flex px-2" :style="{ justifyContent: 'space-around' }">
                        <span v-for="(s, i) in metrics.monthly_series" :key="i" class="text-[11px] text-muted-foreground text-center">{{ s.label }}</span>
                    </div>
                </div>
                <div v-else class="flex h-40 items-center justify-center text-sm text-muted-foreground">
                    Sem dados suficientes
                </div>
            </div>

            <!-- ── Linha 4: Uso da plataforma + Receita por plano ── -->
            <div class="grid gap-6 lg:grid-cols-2">

                <!-- Uso da plataforma -->
                <div class="rounded-2xl border border-border bg-card p-6">
                    <div class="mb-5">
                        <h2 class="text-base font-semibold">Uso da Plataforma</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Agendamentos & contas ativas</p>
                    </div>

                    <!-- Bookings sparkline -->
                    <div v-if="bookingsChart" class="mb-5">
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-xs text-muted-foreground">Agendamentos por mês</span>
                            <span class="text-xs font-semibold text-foreground">{{ fmtNum(metrics.bookings_this_month) }} esse mês</span>
                        </div>
                        <svg :viewBox="`0 0 ${bookingsChart.W} ${bookingsChart.H}`" preserveAspectRatio="none" class="h-20 w-full">
                            <defs>
                                <linearGradient id="bkGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="currentColor" stop-opacity="0.12" />
                                    <stop offset="100%" stop-color="currentColor" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <path :d="bookingsChart.areaD" fill="url(#bkGrad)" class="text-foreground" />
                            <path :d="bookingsChart.pathD" fill="none" stroke="currentColor" stroke-width="1.5" stroke-opacity="0.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <!-- Stats grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-muted/40 p-3">
                            <p class="text-[11px] text-muted-foreground">Total de agendamentos</p>
                            <p class="mt-1 text-xl font-bold text-foreground">{{ fmtNum(metrics.total_bookings) }}</p>
                        </div>
                        <div class="rounded-xl bg-muted/40 p-3">
                            <p class="text-[11px] text-muted-foreground">Prestadores ativos</p>
                            <p class="mt-1 text-xl font-bold text-foreground">{{ fmtNum(metrics.active_providers) }}</p>
                        </div>
                        <div class="rounded-xl bg-muted/40 p-3">
                            <p class="text-[11px] text-muted-foreground">Total de prestadores</p>
                            <p class="mt-1 text-xl font-bold text-foreground">{{ fmtNum(metrics.total_providers) }}</p>
                        </div>
                        <div class="rounded-xl bg-muted/40 p-3">
                            <p class="text-[11px] text-muted-foreground">Usuários cadastrados</p>
                            <p class="mt-1 text-xl font-bold text-foreground">{{ fmtNum(metrics.total_users) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Receita por plano -->
                <div class="rounded-2xl border border-border bg-card p-6">
                    <div class="mb-5">
                        <h2 class="text-base font-semibold">Receita por Plano</h2>
                        <p class="text-xs text-muted-foreground mt-0.5">Distribuição do MRR entre os planos</p>
                    </div>

                    <div v-if="planRevenue.length" class="space-y-4">
                        <div v-for="(plan, i) in planRevenue" :key="plan.name" class="space-y-1.5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 font-medium">
                                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-foreground/50"></span>
                                    {{ plan.name }}
                                </span>
                                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                                    <span class="font-semibold text-foreground">{{ fmtBRL(plan.revenue_cents) }}</span>
                                    <span>{{ plan.active_count }} assinantes</span>
                                </div>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-foreground/40 transition-all duration-700"
                                    :style="{ width: plan.pct + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex h-48 items-center justify-center text-sm text-muted-foreground">
                        Nenhum plano com assinantes ativos
                    </div>

                    <!-- Total MRR summary -->
                    <div v-if="planRevenue.length" class="mt-5 flex items-center justify-between rounded-xl bg-muted/40 px-4 py-3">
                        <span class="text-sm text-muted-foreground">MRR Total</span>
                        <span class="text-lg font-bold text-foreground">{{ fmtBRL(metrics.mrr_cents) }}</span>
                    </div>
                </div>
            </div>

            <!-- ── Linha 5: Receita por Segmento ── -->
            <div class="rounded-2xl border border-border bg-card p-6">
                <div class="mb-5">
                    <h2 class="text-base font-semibold">Receita por Segmento</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">MRR distribuído por categoria de prestador</p>
                </div>

                <div v-if="segmentRevenue.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="(seg, i) in segmentRevenue"
                        :key="seg.segment"
                        class="flex items-center gap-4 rounded-xl border border-border bg-muted/20 p-4"
                    >
                        <!-- Initials badge -->
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted text-xs font-bold text-muted-foreground">
                            {{ seg.segment.slice(0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate text-foreground">{{ seg.segment }}</p>
                            <p class="text-xs text-muted-foreground">{{ seg.count }} prestador{{ seg.count !== 1 ? 'es' : '' }}</p>
                            <!-- Progress bar -->
                            <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-foreground/40 transition-all duration-700"
                                    :style="{ width: seg.pct + '%' }"
                                ></div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-bold text-foreground">{{ fmtBRL(seg.revenue_cents) }}</p>
                            <p class="text-[11px] text-muted-foreground">{{ seg.pct }}% do MRR</p>
                        </div>
                    </div>
                </div>
                <div v-else class="flex h-24 items-center justify-center text-sm text-muted-foreground">
                    Nenhum prestador com segmento definido
                </div>
            </div>

        </section>
    </AuthenticatedLayout>
</template>

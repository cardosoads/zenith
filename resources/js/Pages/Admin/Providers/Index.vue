<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Search,
    ShieldCheck,
    ShieldOff,
    Users,
    CheckCircle2,
    Clock,
    AlertTriangle,
    ChevronDown,
    XCircle,
} from 'lucide-vue-next';

const props = defineProps({
    providers: {
        type: Array,
        default: () => [],
    },
});

// ── Search & Filter ───────────────────────────────────────────────────
const search = ref('');
const filterStatus = ref('all');

const filteredProviders = computed(() => {
    return props.providers.filter((p) => {
        const matchesSearch =
            !search.value ||
            p.display_name?.toLowerCase().includes(search.value.toLowerCase()) ||
            p.user?.email?.toLowerCase().includes(search.value.toLowerCase());

        const matchesStatus =
            filterStatus.value === 'all' || p.status === filterStatus.value;

        return matchesSearch && matchesStatus;
    });
});

// ── KPI Stats ─────────────────────────────────────────────────────────
const stats = computed(() => [
    {
        label: 'Total de Prestadores',
        value: props.providers.length,
        icon: Users,
        color: 'blue',
    },
    {
        label: 'Ativos',
        value: props.providers.filter((p) => p.status === 'active').length,
        icon: CheckCircle2,
        color: 'emerald',
    },
    {
        label: 'Pendentes',
        value: props.providers.filter((p) => p.status === 'pending').length,
        icon: Clock,
        color: 'amber',
    },
    {
        label: 'Suspensos',
        value: props.providers.filter((p) => p.status === 'suspended').length,
        icon: AlertTriangle,
        color: 'rose',
    },
]);

const colorMap = {
    blue:    { bg: 'bg-muted',    text: 'text-foreground',         border: 'border-border' },
    emerald: { bg: 'bg-muted',    text: 'text-foreground',         border: 'border-border' },
    amber:   { bg: 'bg-muted',    text: 'text-muted-foreground',   border: 'border-border' },
    rose:    { bg: 'bg-muted',    text: 'text-muted-foreground',   border: 'border-border' },
};

// ── Status badge helpers ──────────────────────────────────────────────
const statusConfig = {
    active:    { label: 'Ativo',     classes: 'bg-muted text-foreground ring-border' },
    pending:   { label: 'Pendente',  classes: 'bg-muted text-muted-foreground ring-border' },
    suspended: { label: 'Suspenso',  classes: 'bg-muted text-muted-foreground ring-border' },
};

const getStatus = (status) =>
    statusConfig[status] ?? { label: status, classes: 'bg-muted text-muted-foreground ring-border' };

// ── Actions ───────────────────────────────────────────────────────────
const activating = ref(null);
const suspending = ref(null);

const activate = (provider) => {
    if (activating.value) return;
    activating.value = provider.id;
    useForm({}).post(route('admin.providers.activate', provider.id), {
        preserveScroll: true,
        onFinish: () => (activating.value = null),
    });
};

const suspend = (provider) => {
    if (suspending.value) return;
    suspending.value = provider.id;
    useForm({}).post(route('admin.providers.suspend', provider.id), {
        preserveScroll: true,
        onFinish: () => (suspending.value = null),
    });
};

// ── Initials helper ───────────────────────────────────────────────────
const initials = (name = '') =>
    name.trim().split(/\s+/).slice(0, 2).map((w) => w[0]?.toUpperCase()).join('');
</script>

<template>
    <Head title="Prestadores" />

    <AuthenticatedLayout>
        <section class="space-y-6">

            <!-- Header -->
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">Admin</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight">Prestadores</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Gerencie e monitore todos os prestadores cadastrados na plataforma.</p>
                </div>
            </div>

            <!-- KPI Stats -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="flex items-center gap-4 rounded-2xl border p-5 bg-card transition-all"
                    :class="colorMap[stat.color].border"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                         :class="colorMap[stat.color].bg">
                        <component :is="stat.icon" class="h-5 w-5" :class="colorMap[stat.color].text" />
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-muted-foreground">{{ stat.label }}</p>
                        <p class="text-2xl font-bold" :class="colorMap[stat.color].text">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="rounded-2xl border border-border bg-card">

                <!-- Toolbar -->
                <div class="flex flex-col gap-3 border-b border-border px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Search -->
                    <div class="relative max-w-sm flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nome ou email..."
                            class="w-full rounded-lg border border-border bg-background py-2 pl-9 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors"
                        />
                    </div>

                    <!-- Status filter -->
                    <div class="relative">
                        <select
                            v-model="filterStatus"
                            class="appearance-none rounded-lg border border-border bg-background py-2 pl-3 pr-8 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors"
                        >
                            <option value="all">Todos os status</option>
                            <option value="active">Ativos</option>
                            <option value="pending">Pendentes</option>
                            <option value="suspended">Suspensos</option>
                        </select>
                        <ChevronDown class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Prestador</th>
                                <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Segmento</th>
                                <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Plano</th>
                                <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Billing</th>
                                <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">

                            <!-- Empty state -->
                            <tr v-if="!filteredProviders.length">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                            <Users class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                        <p class="text-sm font-medium text-foreground">Nenhum prestador encontrado</p>
                                        <p class="text-xs text-muted-foreground">Tente ajustar os filtros de busca.</p>
                                    </div>
                                </td>
                            </tr>

                            <!-- Rows -->
                            <tr
                                v-for="provider in filteredProviders"
                                :key="provider.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <!-- Name / email -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-bold text-muted-foreground"
                                        >
                                            {{ initials(provider.display_name) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-foreground">{{ provider.display_name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ provider.user?.email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Segment -->
                                <td class="px-6 py-4">
                                    <span v-if="provider.segment" class="text-sm text-foreground">{{ provider.segment }}</span>
                                    <span v-else class="text-muted-foreground">—</span>
                                </td>

                                <!-- Plan -->
                                <td class="px-6 py-4">
                                    <span
                                        v-if="provider.current_subscription?.plan?.name"
                                        class="inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-foreground"
                                    >
                                        {{ provider.current_subscription.plan.name }}
                                    </span>
                                    <span v-else class="text-muted-foreground text-xs">Sem plano</span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1"
                                        :class="getStatus(provider.status).classes"
                                    >
                                        {{ getStatus(provider.status).label }}
                                    </span>
                                </td>

                                <!-- Billing -->
                                <td class="px-6 py-4">
                                    <span class="text-xs text-muted-foreground capitalize">
                                        {{ provider.billing_status ?? '—' }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            v-if="provider.status !== 'active'"
                                            @click="activate(provider)"
                                            :disabled="activating === provider.id"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-border bg-muted px-3 py-1.5 text-xs font-semibold text-foreground transition-colors hover:bg-accent disabled:opacity-50"
                                        >
                                            <ShieldCheck class="h-3.5 w-3.5" />
                                            {{ activating === provider.id ? 'Ativando...' : 'Ativar' }}
                                        </button>
                                        <button
                                            v-if="provider.status !== 'suspended'"
                                            @click="suspend(provider)"
                                            :disabled="suspending === provider.id"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-border bg-muted px-3 py-1.5 text-xs font-semibold text-muted-foreground transition-colors hover:bg-accent disabled:opacity-50"
                                        >
                                            <ShieldOff class="h-3.5 w-3.5" />
                                            {{ suspending === provider.id ? 'Suspendendo...' : 'Suspender' }}
                                        </button>
                                        <!-- Both actions shown: when active show only suspend, when pending/suspended show both or just activate -->
                                        <span
                                            v-if="provider.status === 'active' && suspending !== provider.id"
                                            class="text-xs text-muted-foreground"
                                        ></span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer count -->
                <div class="flex items-center justify-between border-t border-border px-6 py-3">
                    <p class="text-xs text-muted-foreground">
                        Exibindo <span class="font-semibold text-foreground">{{ filteredProviders.length }}</span>
                        de <span class="font-semibold text-foreground">{{ providers.length }}</span> prestadores
                    </p>
                </div>
            </div>

        </section>
    </AuthenticatedLayout>
</template>

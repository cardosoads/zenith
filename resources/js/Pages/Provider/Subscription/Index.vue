<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    CreditCard,
    ShieldCheck,
    Zap,
    ArrowRight,
    CheckCircle2,
    Clock,
    UserCircle,
    CalendarDays,
    Users,
    AlertCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    subscription: Object,
    plans: Array,
});

const statusConfig = {
    trialing: { label: 'Teste grátis', classes: 'bg-amber-500/10 text-amber-600' },
    active: { label: 'Ativo', classes: 'bg-emerald-500/10 text-emerald-600' },
    past_due: { label: 'Pagamento pendente', classes: 'bg-red-500/10 text-red-600' },
    cancelled: { label: 'Cancelado', classes: 'bg-gray-500/10 text-gray-500' },
    expired: { label: 'Expirado', classes: 'bg-red-500/10 text-red-600' },
    pending: { label: 'Pendente', classes: 'bg-amber-500/10 text-amber-600' },
    none: { label: 'Sem assinatura', classes: 'bg-gray-500/10 text-gray-500' },
};

const invoiceStatusConfig = {
    trialing: { label: 'Trial', classes: 'bg-amber-500/10 text-amber-600' },
    active: { label: 'Pago', classes: 'bg-emerald-500/10 text-emerald-600' },
    pending: { label: 'Pendente', classes: 'bg-amber-500/10 text-amber-600' },
    past_due: { label: 'Atrasado', classes: 'bg-red-500/10 text-red-600' },
    cancelled: { label: 'Cancelado', classes: 'bg-gray-500/10 text-gray-500' },
    expired: { label: 'Expirado', classes: 'bg-red-500/10 text-red-600' },
};

const currentStatus = computed(() => {
    return statusConfig[props.subscription.status] ?? statusConfig.none;
});

const hasSubscription = computed(() => props.subscription.status !== 'none');
const isTrialing = computed(() => props.subscription.is_trialing);
const isActive = computed(() => props.subscription.status === 'active');
const isCancelledOrExpired = computed(() =>
    ['cancelled', 'expired'].includes(props.subscription.status)
);

const planInfoLine = computed(() => {
    if (!hasSubscription.value) {
        return 'Nenhum plano ativo';
    }
    if (isTrialing.value) {
        return `Gratuito — Teste expira em ${formatDate(props.subscription.trial_ends_at)}`;
    }
    if (isCancelledOrExpired.value) {
        return `${formatCurrency(props.subscription.price)}/mês — Assinatura encerrada`;
    }
    if (props.subscription.renewal_date) {
        return `${formatCurrency(props.subscription.price)}/mês — Renova em ${formatDate(props.subscription.renewal_date)}`;
    }
    return `${formatCurrency(props.subscription.price)}/mês`;
});

const formatCurrency = (val) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

const formatDate = (date) => {
    if (!date) return '--/--/----';
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const getUsagePercentage = (current, limit) => {
    if (!limit) return 0;
    return Math.min(Math.round((current / limit) * 100), 100);
};

const getInvoiceStatus = (status) => {
    return invoiceStatusConfig[status] ?? { label: status, classes: 'bg-gray-500/10 text-gray-500' };
};
</script>

<template>
    <Head title="Assinatura" />

    <AuthenticatedLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">Assinatura</h1>
            <p class="mt-1 text-muted-foreground">
                Gerencie seu plano, método de pagamento e faturas.
            </p>
        </div>

        <!-- Empty State: No Subscription -->
        <div v-if="!hasSubscription" class="grid gap-8">
            <div class="rounded-2xl border border-border bg-card p-12 shadow-sm text-center">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-muted">
                    <ShieldCheck class="h-10 w-10 text-muted-foreground" />
                </div>
                <h2 class="mt-6 text-xl font-bold text-foreground">Você ainda não tem um plano ativo</h2>
                <p class="mt-2 text-muted-foreground">
                    Assine um plano para começar a usar o Zenith.
                </p>
                <Link
                    :href="route('onboarding.show')"
                    class="mt-6 inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition-all hover:opacity-90 active:scale-95"
                >
                    Ver planos disponíveis
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
        </div>

        <!-- Active Subscription Content -->
        <div v-else class="grid gap-8">
            <!-- Trial Banner -->
            <div
                v-if="isTrialing"
                class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-6 dark:border-amber-800 dark:from-amber-950/30 dark:to-orange-950/30"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                            <Clock class="h-6 w-6 text-amber-600" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">Período de teste</h3>
                            <p class="text-sm text-amber-700 dark:text-amber-400">
                                Restam <span class="font-bold">{{ subscription.trial_days_remaining }} dias</span> do seu teste grátis.
                                Assine um plano para continuar usando após o teste.
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('onboarding.show')"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-amber-700 active:scale-95"
                    >
                        Assinar agora
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>

            <!-- Plan Status Card -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="flex flex-col gap-6 p-8 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-6">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary shadow-lg shadow-primary/20">
                            <ShieldCheck class="h-8 w-8 text-primary-foreground" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-bold text-foreground">Plano {{ subscription.plan_name }}</h2>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold uppercase tracking-wider"
                                    :class="currentStatus.classes"
                                >
                                    {{ currentStatus.label }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ planInfoLine }}
                            </p>
                        </div>
                    </div>
                    <Link
                        :href="route('onboarding.show')"
                        class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold text-foreground shadow-sm transition-all hover:bg-accent active:scale-95"
                    >
                        Trocar plano
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Usage Section -->
                <div class="border-t border-border bg-muted/30 p-8">
                    <h3 class="mb-6 text-sm font-semibold uppercase tracking-wider text-muted-foreground">Uso do plano</h3>
                    <div class="grid gap-8 md:grid-cols-3">
                        <!-- Professionals Usage -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground">Profissionais</span>
                                <span class="text-sm font-bold text-foreground">
                                    <template v-if="subscription.usage.professionals.limit !== null">
                                        {{ subscription.usage.professionals.current }} / {{ subscription.usage.professionals.limit }}
                                    </template>
                                    <template v-else>
                                        {{ subscription.usage.professionals.current }}
                                        <span class="ml-1 text-xs font-normal text-muted-foreground">Ilimitado</span>
                                    </template>
                                </span>
                            </div>
                            <template v-if="subscription.usage.professionals.limit !== null">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-500"
                                        :style="{ width: getUsagePercentage(subscription.usage.professionals.current, subscription.usage.professionals.limit) + '%' }"
                                    ></div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                    <div class="h-full w-1/6 rounded-full bg-primary/30"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Appointments Usage -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground">Agendamentos</span>
                                <span class="text-sm font-bold text-foreground">
                                    <template v-if="subscription.usage.appointments.limit !== null">
                                        {{ subscription.usage.appointments.current }} / {{ subscription.usage.appointments.limit }}
                                    </template>
                                    <template v-else>
                                        {{ subscription.usage.appointments.current }}
                                        <span class="ml-1 text-xs font-normal text-muted-foreground">Ilimitado</span>
                                    </template>
                                </span>
                            </div>
                            <template v-if="subscription.usage.appointments.limit !== null">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-500"
                                        :style="{ width: getUsagePercentage(subscription.usage.appointments.current, subscription.usage.appointments.limit) + '%' }"
                                    ></div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                    <div class="h-full w-1/6 rounded-full bg-primary/30"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Customers Usage -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground">Clientes</span>
                                <span class="text-sm font-bold text-foreground">
                                    <template v-if="subscription.usage.customers.limit !== null">
                                        {{ subscription.usage.customers.current }} / {{ subscription.usage.customers.limit }}
                                    </template>
                                    <template v-else>
                                        {{ subscription.usage.customers.current }}
                                        <span class="ml-1 text-xs font-normal text-muted-foreground">Ilimitado</span>
                                    </template>
                                </span>
                            </div>
                            <template v-if="subscription.usage.customers.limit !== null">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                    <div
                                        class="h-full rounded-full bg-primary transition-all duration-500"
                                        :style="{ width: getUsagePercentage(subscription.usage.customers.current, subscription.usage.customers.limit) + '%' }"
                                    ></div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                    <div class="h-full w-1/6 rounded-full bg-primary/30"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Card -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="border-b border-border px-8 py-6">
                    <h2 class="text-lg font-bold text-foreground">Método de pagamento</h2>
                </div>
                <div class="p-8">
                    <!-- Trial: no payment method -->
                    <div v-if="isTrialing" class="flex items-center gap-4 rounded-xl border border-dashed border-amber-300 bg-amber-50/50 p-6 max-w-md dark:border-amber-700 dark:bg-amber-950/20">
                        <div class="flex h-12 w-16 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/30">
                            <CreditCard class="h-6 w-6 text-amber-500" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-foreground">Nenhum método de pagamento</p>
                            <p class="text-xs text-muted-foreground">Período de teste ativo — adicione um ao assinar.</p>
                        </div>
                        <Link
                            :href="route('onboarding.show')"
                            class="text-sm font-semibold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            Assinar
                        </Link>
                    </div>

                    <!-- Paid: show card info -->
                    <div v-else class="flex items-center gap-4 rounded-xl border border-border bg-muted/20 p-6 max-w-md">
                        <div class="flex h-12 w-16 items-center justify-center rounded-lg border border-border bg-card shadow-inner">
                            <CreditCard class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-foreground">
                                &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; {{ subscription.payment_method.last4 }}
                            </p>
                            <p class="text-xs text-muted-foreground">Expira em {{ subscription.payment_method.expiry }}</p>
                        </div>
                        <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- Invoice History Table -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-md">
                <div class="px-8 py-6">
                    <h2 class="text-lg font-bold text-foreground">Histórico de faturas</h2>
                </div>

                <!-- Empty state -->
                <div v-if="!subscription.invoices || subscription.invoices.length === 0" class="px-8 pb-8">
                    <div class="rounded-xl border border-dashed border-border bg-muted/20 p-8 text-center">
                        <CalendarDays class="mx-auto h-8 w-8 text-muted-foreground" />
                        <p class="mt-3 text-sm font-medium text-muted-foreground">Nenhuma fatura encontrada</p>
                    </div>
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-y border-border bg-muted/30 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <th class="px-8 py-4">Fatura</th>
                                <th class="px-8 py-4">Data</th>
                                <th class="px-8 py-4">Plano</th>
                                <th class="px-8 py-4">Valor</th>
                                <th class="px-8 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="invoice in subscription.invoices"
                                :key="invoice.id"
                                class="group transition-colors hover:bg-accent/50"
                            >
                                <td class="px-8 py-4">
                                    <span class="text-sm font-bold text-foreground">{{ invoice.id }}</span>
                                </td>
                                <td class="px-8 py-4">
                                    <span class="text-sm text-muted-foreground">{{ formatDate(invoice.date) }}</span>
                                </td>
                                <td class="px-8 py-4">
                                    <span class="text-sm text-foreground">{{ invoice.plan }}</span>
                                </td>
                                <td class="px-8 py-4">
                                    <span class="text-sm font-bold text-foreground">{{ formatCurrency(invoice.amount) }}</span>
                                </td>
                                <td class="px-8 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold capitalize"
                                        :class="getInvoiceStatus(invoice.status).classes"
                                    >
                                        {{ getInvoiceStatus(invoice.status).label }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

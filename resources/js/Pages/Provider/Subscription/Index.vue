<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CreditCard, 
    ShieldCheck, 
    Zap, 
    ArrowRight, 
    Download, 
    CheckCircle2,
    Clock,
    UserCircle,
    CalendarDays,
    Users
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    subscription: Object
});

const formatCurrency = (val) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const getUsagePercentage = (current, limit) => {
    if (!limit) return 0;
    return Math.min(Math.round((current / limit) * 100), 100);
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

        <div class="grid gap-8">
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
                                <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-600 uppercase tracking-wider">
                                    Ativo
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ formatCurrency(subscription.price) }}/mês — Renova em {{ formatDate(subscription.renewal_date) }}
                            </p>
                        </div>
                    </div>
                    <button class="inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold text-foreground shadow-sm transition-all hover:bg-accent active:scale-95">
                        Trocar plano
                        <ArrowRight class="h-4 w-4" />
                    </button>
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
                                    {{ subscription.usage.professionals.current }} / {{ subscription.usage.professionals.limit }}
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                <div 
                                    class="h-full rounded-full bg-primary transition-all duration-500" 
                                    :style="{ width: getUsagePercentage(subscription.usage.professionals.current, subscription.usage.professionals.limit) + '%' }"
                                ></div>
                            </div>
                        </div>

                        <!-- Appointments Usage -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground">Agendamentos</span>
                                <span class="text-sm font-bold text-foreground">
                                    {{ subscription.usage.appointments.current }} / {{ subscription.usage.appointments.limit || '∞' }}
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                <div 
                                    class="h-full rounded-full bg-primary transition-all duration-500" 
                                    :style="{ width: '50%' }" 
                                ></div>
                            </div>
                        </div>

                        <!-- Customers Usage -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-foreground">Clientes</span>
                                <span class="text-sm font-bold text-foreground">
                                    {{ subscription.usage.customers.current }} / {{ subscription.usage.customers.limit || '∞' }}
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                                <div 
                                    class="h-full rounded-full bg-primary transition-all duration-500" 
                                    :style="{ width: '40%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-md overflow-hidden">
                <div class="flex items-center justify-between border-b border-border px-8 py-6">
                    <h2 class="text-lg font-bold text-foreground">Método de pagamento</h2>
                    <button class="text-sm font-semibold text-primary hover:underline">Alterar</button>
                </div>
                <div class="p-8">
                    <div class="flex items-center gap-4 rounded-xl border border-border bg-muted/20 p-6 max-w-md">
                        <div class="flex h-12 w-16 items-center justify-center rounded-lg border border-border bg-card shadow-inner">
                            <CreditCard class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-foreground">•••• •••• •••• {{ subscription.payment_method.last4 }}</p>
                            <p class="text-xs text-muted-foreground">Expira em {{ subscription.payment_method.expiry }}</p>
                        </div>
                        <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- Invoice History Table -->
            <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-md overflow-hidden">
                <div class="px-8 py-6">
                    <h2 class="text-lg font-bold text-foreground">Histórico de faturas</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-y border-border bg-muted/30 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                <th class="px-8 py-4">Fatura</th>
                                <th class="px-8 py-4">Data</th>
                                <th class="px-8 py-4">Plano</th>
                                <th class="px-8 py-4">Valor</th>
                                <th class="px-8 py-4">Status</th>
                                <th class="px-8 py-4 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="invoice in subscription.invoices" :key="invoice.id" class="group transition-colors hover:bg-accent/50">
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
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-bold text-emerald-600 capitalize">
                                        Pago
                                    </span>
                                </td>
                                <td class="px-8 py-4 text-right">
                                    <button class="rounded-lg p-2 text-muted-foreground hover:bg-accent hover:text-foreground transition-all">
                                        <Download class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

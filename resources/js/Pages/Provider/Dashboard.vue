<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import UpcomingAppointments from '@/Components/Provider/UpcomingAppointments.vue';
import RecentActivity from '@/Components/Provider/RecentActivity.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    CalendarDays, 
    Users, 
    DollarSign, 
    TrendingUp,
    Plus,
    LayoutGrid
} from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    auth: Object,
    profile: Object,
    metrics: Object,
    serviceMetrics: Array,
    recentBookings: Array,
    recentServices: Array,
    selectedAgendaId: Number,
});

const firstName = computed(() => {
    return props.auth.user.name.split(' ')[0];
});

const formatMoney = (cents) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format((cents ?? 0) / 100);
};

// Transform recent bookings for the RecentActivity component
const transformedActivities = computed(() => {
    return props.recentBookings.map(booking => {
        return {
            customer_name: booking.customer_name,
            service: booking.service?.name || 'Serviço',
            status: booking.status === 'cancelled' ? 'cancelado' : booking.status,
            time: new Date(booking.created_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
        };
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Welcome Section -->
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Bom dia, {{ firstName }}
                </h1>
                <p class="mt-1 text-muted-foreground">
                    Aqui está o resumo da sua operação hoje.
                </p>
            </div>
            
            <div v-if="selectedAgendaId" class="flex items-center gap-3">
                <Link 
                    :href="route('bookings.index')" 
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 transition-all active:scale-95"
                >
                    <Plus class="h-4 w-4" />
                    Novo Agendamento
                </Link>
            </div>
        </div>

        <!-- No Agenda State -->
        <div v-if="!selectedAgendaId" class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-border bg-card p-12 text-center">
            <div class="mb-4 rounded-full bg-accent p-4">
                <LayoutGrid class="h-8 w-8 text-primary" />
            </div>
            <h2 class="text-xl font-bold text-foreground">Crie sua primeira agenda</h2>
            <p class="mx-auto mt-2 max-w-sm text-muted-foreground">
                Você precisa configurar uma agenda para começar a oferecer seus serviços e receber agendamentos.
            </p>
            <Link 
                :href="route('provider.agendas.index')" 
                class="mt-6 inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 hover:bg-primary/90 transition-all"
            >
                Configurar Agendas
            </Link>
        </div>

        <template v-else>
            <!-- Stats Grid -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Agendamentos"
                    :value="metrics.bookingsTotal"
                    change="+12% que ontem"
                    changeType="positive"
                    :icon="CalendarDays"
                />
                <StatCard
                    title="Serviços Ativos"
                    :value="metrics.services"
                    change="Mantido"
                    changeType="neutral"
                    :icon="LayoutGrid"
                />
                <StatCard
                    title="Pagos"
                    :value="metrics.bookingsPaid"
                    change="+5 essa semana"
                    changeType="positive"
                    :icon="DollarSign"
                />
                <StatCard
                    title="Pendentes"
                    :value="metrics.bookingsPending"
                    change="-2% vs média"
                    changeType="negative"
                    :icon="TrendingUp"
                />
            </div>

            <!-- Content Row -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <UpcomingAppointments :appointments="recentBookings" />
                <RecentActivity :activities="transformedActivities" />
            </div>
        </template>
    </AuthenticatedLayout>
</template>


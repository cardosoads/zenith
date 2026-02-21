<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    Users, 
    Search, 
    Mail, 
    Phone, 
    Calendar, 
    ChevronRight,
    MoreHorizontal
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    customers: Array,
    selectedAgendaId: [Number, null],
});

const searchQuery = ref('');

const filteredCustomers = computed(() => {
    return props.customers.filter(customer => {
        const search = searchQuery.value.toLowerCase();
        return (
            customer.customer_name?.toLowerCase().includes(search) ||
            customer.customer_email?.toLowerCase().includes(search) ||
            customer.customer_phone?.toLowerCase().includes(search)
        );
    });
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Clientes</h1>
                <p class="mt-1 text-muted-foreground">
                    Gerencie a base de pessoas que utilizam seus serviços.
                </p>
            </div>
        </div>

        <div class="mb-6 overflow-hidden rounded-2xl border border-border bg-card shadow-sm">
            <div class="flex flex-col border-b border-border p-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                <div class="relative w-full sm:max-w-xs">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar cliente..."
                        class="w-full rounded-lg border border-border bg-muted/50 py-2 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-all duration-200"
                    />
                </div>
                
                <div class="mt-4 flex items-center gap-2 sm:mt-0">
                    <span class="text-xs font-medium text-muted-foreground">
                        {{ filteredCustomers.length }} {{ filteredCustomers.length === 1 ? 'cliente encontrado' : 'clientes encontrados' }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-border bg-muted/30 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                            <th class="px-6 py-4">Nome</th>
                            <th class="px-6 py-4">Contato</th>
                            <th class="px-6 py-4">Agendamentos</th>
                            <th class="px-6 py-4">Última Visita</th>
                            <th class="px-6 py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="customer in filteredCustomers" :key="customer.customer_email || customer.customer_phone" class="group transition-colors hover:bg-accent/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary capitalize">
                                        {{ customer.customer_name[0] }}
                                    </div>
                                    <span class="text-sm font-semibold text-foreground">{{ customer.customer_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <div v-if="customer.customer_email" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <Mail class="h-3 w-3" />
                                        {{ customer.customer_email }}
                                    </div>
                                    <div v-if="customer.customer_phone" class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <Phone class="h-3 w-3" />
                                        {{ customer.customer_phone }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                    {{ customer.bookings_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Calendar class="h-3.5 w-3.5" />
                                    {{ formatDate(customer.last_booking_at) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="rounded-lg p-2 text-muted-foreground hover:bg-accent hover:text-foreground transition-all">
                                    <MoreHorizontal class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!filteredCustomers.length">
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-muted-foreground">
                                <div class="flex flex-col items-center gap-2">
                                    <Users class="mb-2 h-10 w-10 text-muted/30" />
                                    <p>Nenhum cliente encontrado.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

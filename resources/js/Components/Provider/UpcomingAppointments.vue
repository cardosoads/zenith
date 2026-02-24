<script setup>
import { CalendarDays, MoreVertical } from 'lucide-vue-next';
import Badge from '@/Components/UI/Badge.vue';

defineProps({
    appointments: {
        type: Array,
        default: () => [],
    },
});

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="rounded-xl border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border px-6 py-4">
            <div class="flex items-center gap-2">
                <CalendarDays class="h-4 w-4 text-muted-foreground" />
                <h2 class="text-sm font-semibold text-foreground">Próximos Agendamentos</h2>
            </div>
            <button class="rounded-md p-1.5 hover:bg-accent text-muted-foreground">
                <MoreVertical class="h-4 w-4" />
            </button>
        </div>
        <div class="divide-y divide-border">
            <div 
                v-for="item in appointments" 
                :key="item.id" 
                class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-accent/50"
            >
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 flex-col items-center justify-center rounded-lg bg-accent text-[10px] font-bold uppercase tracking-tighter">
                        <span class="text-xs">{{ formatTime(item.starts_at) }}</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-medium text-foreground">{{ item.customer_name }}</span>
                        <span class="text-xs text-muted-foreground">{{ item.service?.name }}</span>
                    </div>
                </div>
                <Badge :tone="item.status === 'confirmed' ? 'success' : 'warning'">
                    {{ 
                        item.status === 'confirmed' ? 'Confirmado' : 
                        item.status === 'pending' ? 'Pendente' : 
                        item.status === 'cancelled' ? 'Cancelado' : 
                        item.status 
                    }}
                </Badge>
            </div>
            <div v-if="!appointments.length" class="flex flex-col items-center justify-center py-12 text-center">
                <p class="text-sm text-muted-foreground">Nenhum agendamento para hoje.</p>
            </div>
        </div>
        <div class="border-t border-border px-6 py-3">
            <button class="text-xs font-medium text-primary hover:underline">
                Ver todos os agendamentos →
            </button>
        </div>
    </div>
</template>

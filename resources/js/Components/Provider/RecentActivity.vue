<script setup>
import { History, CheckCircle2, XCircle, Clock } from 'lucide-vue-next';

defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
});

const getStatusIcon = (status) => {
    switch (status) {
        case 'confirmed': return CheckCircle2;
        case 'cancelled':
        case 'cancelado': return XCircle;
        default: return Clock;
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'confirmed': return 'text-emerald-500';
        case 'cancelled':
        case 'cancelado': return 'text-rose-500';
        default: return 'text-amber-500';
    }
};
</script>

<template>
    <div class="rounded-xl border border-border bg-card shadow-sm">
        <div class="flex items-center gap-2 border-b border-border px-6 py-4">
            <History class="h-4 w-4 text-muted-foreground" />
            <h2 class="text-sm font-semibold text-foreground">Atividade Recente</h2>
        </div>
        <div class="p-6">
            <div class="relative space-y-6 before:absolute before:left-[11px] before:top-2 before:h-[calc(100%-16px)] before:w-px before:bg-border">
                <div v-for="(activity, index) in activities" :key="index" class="relative pl-8">
                    <span class="absolute left-0 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-card ring-4 ring-card">
                        <component 
                            :is="getStatusIcon(activity.status)" 
                            class="h-4 w-4" 
                            :class="getStatusClass(activity.status)"
                        />
                    </span>
                    <div class="flex flex-col gap-0.5">
                        <p class="text-sm text-foreground">
                            <span class="font-medium">{{ activity.customer_name }}</span>
                            {{ 
                                activity.status === 'confirmed' ? ' confirmou um ' : 
                                (activity.status === 'cancelled' || activity.status === 'cancelado') ? ' cancelou o ' : ' solicitou um ' 
                            }}
                            agendamento de <span class="font-medium">{{ activity.service }}</span>
                        </p>
                        <span class="text-xs text-muted-foreground">{{ activity.time }}</span>
                    </div>
                </div>
                <div v-if="!activities.length" class="text-center py-4">
                    <p class="text-sm text-muted-foreground">Sem atividades recentes.</p>
                </div>
            </div>
        </div>
        <div class="border-t border-border px-6 py-3">
            <button class="text-xs font-medium text-primary hover:underline">
                Ver histórico completo →
            </button>
        </div>
    </div>
</template>

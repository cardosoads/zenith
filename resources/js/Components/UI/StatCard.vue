<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    change: {
        type: String,
        required: false,
    },
    changeType: {
        type: String,
        default: 'positive', // 'positive' | 'negative' | 'neutral'
        validator: (value) => ['positive', 'negative', 'neutral'].includes(value),
    },
    icon: {
        type: [Object, Function],
        required: true,
    },
});

const changeClass = computed(() => {
    if (props.changeType === 'positive') return 'text-emerald-600 dark:text-emerald-400';
    if (props.changeType === 'negative') return 'text-rose-600 dark:text-rose-400';
    return 'text-muted-foreground';
});
</script>

<template>
    <div class="flex flex-col gap-3 rounded-xl border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">
                {{ title }}
            </span>
            <span class="rounded-lg bg-accent/50 p-2 text-muted-foreground">
                <component :is="icon" class="h-4 w-4" />
            </span>
        </div>
        <div class="flex flex-col gap-1">
            <span class="text-3xl font-bold tracking-tight text-foreground">
                {{ value }}
            </span>
            <div v-if="change" class="flex items-center gap-1.5 text-xs font-medium">
                <span :class="changeClass">{{ change }}</span>
            </div>
        </div>
    </div>
</template>

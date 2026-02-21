<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary', // primary | secondary | outline | ghost | danger
    },
    size: {
        type: String,
        default: 'md', // sm | md | lg
    },
});

const variants = {
    primary: 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
    secondary: 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
    outline: 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground',
    ghost: 'hover:bg-accent hover:text-accent-foreground',
    danger: 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
};

const sizes = {
    sm: 'h-8 px-3 text-xs',
    md: 'h-10 px-4 py-2 text-sm',
    lg: 'h-11 px-8 text-base',
};

const classes = computed(() => {
    return [
        'inline-flex items-center justify-center rounded-lg font-medium transition-all focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 active:scale-95',
        variants[props.variant] || variants.primary,
        sizes[props.size] || sizes.md,
    ].join(' ');
});
</script>

<template>
    <button :type="type" :class="classes">
        <slot />
    </button>
</template>


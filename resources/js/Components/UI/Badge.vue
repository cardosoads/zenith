<script setup>
const props = defineProps({
    tone: {
        type: String,
        default: 'neutral',
    },
});

const map = {
    neutral: 'border-[var(--za-border-strong)] text-[var(--za-text)]',
    success: 'border-[var(--light-green)] text-[var(--light-green)]',
    warning: 'border-[var(--honey-bronze)] text-[var(--honey-bronze)]',
    info: 'border-[var(--sky-blue)] text-[var(--sky-blue)]',
};
</script>

<template>
    <span class="za-chip" :class="map[props.tone] ?? map.neutral">
        <slot />
    </span>
</template>

<script setup>
defineProps({
    value: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <label class="mb-1 block text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>

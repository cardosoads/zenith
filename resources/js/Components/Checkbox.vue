<script setup>
defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
});

defineEmits(['update:checked']);
</script>

<template>
    <input
        type="checkbox"
        :checked="checked"
        class="h-4 w-4 border border-[var(--za-border-strong)] bg-[var(--za-surface)]"
        @input="$emit('update:checked', $event.target.checked)"
    />
</template>

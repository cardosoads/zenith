<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="za-button za-button-neutral">
        <slot />
    </button>
</template>

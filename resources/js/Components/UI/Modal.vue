<script setup>
const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);
</script>

<template>
    <div v-if="props.open" class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" @click.self="emit('close')">
        <div class="bg-white border border-gray-200 rounded-lg shadow-xl w-full max-w-xl p-6">
            <slot />
        </div>
    </div>
</template>

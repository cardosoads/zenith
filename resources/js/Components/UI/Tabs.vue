<script setup>
const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <div class="flex flex-wrap gap-2 border-b border-[var(--za-border)] pb-2">
        <button
            v-for="item in props.items"
            :key="item.value"
            class="za-chip"
            :class="item.value === props.modelValue ? 'border-[var(--za-border-strong)] text-[var(--za-text)]' : ''"
            @click="emit('update:modelValue', item.value)"
        >
            {{ item.label }}
        </button>
    </div>
</template>

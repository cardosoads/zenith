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
    <div 
        v-if="props.open" 
        class="fixed inset-0 z-[100] grid place-items-center bg-black/60 backdrop-blur-sm p-4 transition-all duration-300"
        @click.self="emit('close')"
    >
        <div class="bg-background border border-border rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-6">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation-fill-mode: forwards;
}
@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes zoom-in-95 {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.fade-in { animation-name: fade-in; }
.zoom-in-95 { animation-name: zoom-in-95; }
</style>

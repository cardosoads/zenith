<script setup>
import Modal from '@/Components/UI/Modal.vue';
import { CheckCircle2, AlertCircle, HelpCircle, X, Loader2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    open: Boolean,
    title: String,
    message: String,
    type: {
        type: String,
        default: 'success', // 'success' | 'error' | 'confirm' | 'loading'
    },
    confirmText: {
        type: String,
        default: 'Confirmar',
    },
    cancelText: {
        type: String,
        default: 'Cancelar',
    },
    loading: Boolean,
});

const emit = defineEmits(['close', 'confirm', 'action']);

const icon = computed(() => {
    if (props.type === 'error') return AlertCircle;
    if (props.type === 'confirm') return HelpCircle;
    if (props.type === 'loading') return Loader2;
    return CheckCircle2;
});

const iconClass = computed(() => {
    if (props.type === 'error') return 'text-rose-500 bg-rose-50';
    if (props.type === 'confirm') return 'text-sky-500 bg-sky-50';
    if (props.type === 'loading') return 'text-slate-500 bg-slate-50';
    return 'text-emerald-500 bg-emerald-50';
});

const primaryButtonClass = computed(() => {
    if (props.type === 'error') return 'bg-rose-600 hover:bg-rose-700 text-white';
    if (props.type === 'confirm') return 'bg-black hover:bg-slate-800 text-white';
    return 'bg-black hover:bg-slate-800 text-white';
});
</script>

<template>
    <Modal :open="open" @close="loading ? null : emit('close')">
        <div class="flex flex-col items-center text-center py-4">
            <!-- Icon -->
            <div :class="['h-16 w-16 rounded-full flex items-center justify-center mb-6', iconClass]">
                <component :is="icon" :class="['h-8 w-8', { 'animate-spin': type === 'loading' }]" />
            </div>

            <!-- Content -->
            <h3 v-if="title" class="text-xl font-bold text-slate-900 mb-2">{{ title }}</h3>
            <p v-if="message" class="text-sm text-slate-500 leading-relaxed mb-8 max-w-xs">
                {{ message }}
            </p>

            <!-- Actions -->
            <div class="w-full space-y-3">
                <button
                    v-if="type !== 'loading'"
                    @click="emit('confirm')"
                    :disabled="loading"
                    :class="['w-full h-12 rounded-xl text-sm font-bold shadow-lg transition-all active:scale-95 flex items-center justify-center gap-2', primaryButtonClass]"
                >
                    <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                    {{ confirmText }}
                </button>
                
                <button
                    v-if="type === 'confirm' && !loading"
                    @click="emit('action')"
                    class="w-full h-10 text-[10px] font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors"
                >
                    {{ cancelText }}
                </button>
                
                <button
                    v-if="(type === 'success' || type === 'error') && !loading"
                    @click="emit('close')"
                    class="w-full h-10 text-xs font-bold text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors"
                >
                    Fechar
                </button>
            </div>
        </div>
    </Modal>
</template>

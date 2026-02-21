<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import Modal from '@/Components/UI/Modal.vue';
import { 
    X, 
    User, 
    Phone, 
    Mail, 
    MapPin,
    Clock, 
    Calendar,
    DollarSign,
    UserCircle,
    ChevronDown,
    ArrowRight
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    open: Boolean,
    booking: Object,
});

const emit = defineEmits(['close']);

const activeTab = ref('detalhes');

const tabs = [
    { id: 'detalhes', label: 'Detalhes' },
    { id: 'documentos', label: 'Documentos' },
    { id: 'atividade', label: 'Atividade' },
];

const formattedDate = computed(() => {
    if (!props.booking?.starts_at) return '';
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).format(new Date(props.booking.starts_at));
});

const formattedDuration = computed(() => {
    const min = props.booking?.service?.duration_minutes || 60;
    const hours = Math.floor(min / 60);
    const rem = min % 60;
    return `${hours}h ${String(rem).padStart(2, '0')}min`;
});

const formattedValue = computed(() => {
    const cents = props.booking?.service?.price_cents || 0;
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(cents / 100);
});
</script>

<template>
    <Modal :open="open" @close="emit('close')">
        <div v-if="booking" class="relative">
            <!-- Close Button Top Right -->
            <button @click="emit('close')" class="absolute -top-1 -right-1 p-2 text-gray-400 hover:text-gray-600 transition-colors">
                <X class="h-4 w-4" />
            </button>

            <!-- Header Section -->
            <div class="px-1 pt-2 pb-6">
                <!-- Tags -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span v-for="tag in ['Quimica', 'Premium', 'Urgente']" :key="tag" 
                          :class="cn('px-2 py-0.5 rounded text-[10px] font-medium leading-tight', 
                                     tag === 'Quimica' ? 'bg-rose-50 text-rose-600' : 
                                     tag === 'Premium' ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600')">
                        {{ tag }}
                    </span>
                </div>

                <h2 class="text-lg font-semibold text-gray-900 leading-tight">
                    {{ booking.service?.name }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ booking.customer_name }} -- {{ formattedValue }}
                </p>
            </div>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-100 mb-6">
                <div class="flex gap-8">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="cn('pb-3 text-sm font-medium transition-all relative', 
                                   activeTab === tab.id ? 'text-gray-900 border-b-2 border-slate-900 -mb-[2px]' : 'text-gray-400 hover:text-gray-600')"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Content Area (Scrollable) -->
            <div class="max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                <div v-if="activeTab === 'detalhes'" class="space-y-8 pb-4">
                    
                    <!-- Informações do Cliente -->
                    <section>
                        <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-4">Informações do Cliente</h4>
                        <div class="border border-gray-100 rounded-xl overflow-hidden divide-y divide-gray-50">
                            <!-- Avatar + Name -->
                            <div class="p-4 flex items-center gap-4 bg-white">
                                <div class="h-10 w-10 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 text-xs font-semibold">
                                    {{ booking.customer_name?.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ booking.customer_name }}</p>
                                    <p class="text-[11px] text-gray-400 font-medium">Cliente</p>
                                </div>
                            </div>
                            <!-- Phone -->
                            <div class="p-4 flex items-center gap-3 bg-white">
                                <Phone class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600">{{ booking.customer_phone || '(11) 91234-5678' }}</span>
                            </div>
                            <!-- Email -->
                            <div class="p-4 flex items-center gap-3 bg-white">
                                <Mail class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600">{{ booking.customer_email || 'cliente@email.com' }}</span>
                            </div>
                            <!-- Address -->
                            <div class="p-4 flex items-center gap-3 bg-white">
                                <MapPin class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-500 italic">Endereço não informado</span>
                            </div>
                        </div>
                    </section>

                    <!-- Detalhes do Serviço -->
                    <section>
                        <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-4">Detalhes do Serviço</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Profissional -->
                            <div class="p-4 border border-gray-100 rounded-xl bg-white space-y-2">
                                <div class="flex items-center gap-2">
                                    <UserCircle class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-[10px] font-medium text-gray-500">Profissional</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">Profissional não definido</p>
                            </div>
                            <!-- Duração -->
                            <div class="p-4 border border-gray-100 rounded-xl bg-white space-y-2">
                                <div class="flex items-center gap-2">
                                    <Clock class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-[10px] font-medium text-gray-500">Duração</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ formattedDuration }}</p>
                            </div>
                            <!-- Data -->
                            <div class="p-4 border border-gray-100 rounded-xl bg-white space-y-2">
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-[10px] font-medium text-gray-500">Data</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">{{ formattedDate }}</p>
                            </div>
                            <!-- Valor -->
                            <div class="p-4 border border-gray-100 rounded-xl bg-white space-y-2">
                                <div class="flex items-center gap-2">
                                    <DollarSign class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-[10px] font-medium text-gray-500">Valor</span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">R$ {{ (booking.service?.price_cents / 100).toLocaleString('pt-BR') }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Pagamento -->
                    <section class="border-t border-gray-100 pt-6">
                        <div class="flex items-center justify-between group cursor-pointer">
                            <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Pagamento</h4>
                            <ChevronDown class="h-4 w-4 text-gray-300 group-hover:text-gray-400 transition-colors" />
                        </div>
                    </section>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="mt-8 border-t border-gray-100 pt-6 flex items-center justify-between">
                <span class="text-xs text-gray-400 font-medium">Progresso: 4/12</span>
                <div class="flex gap-2">
                    <button @click="emit('close')" class="px-6 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                        Fechar
                    </button>
                    <Link :href="route('bookings.show', booking.id)" class="px-6 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition-all">
                        Editar
                    </Link>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>

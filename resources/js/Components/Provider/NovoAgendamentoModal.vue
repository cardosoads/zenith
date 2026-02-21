<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/UI/Modal.vue';
import { X, Calendar, Clock, User, Scissors, Check } from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    services: { type: Array, default: () => [] },
    agendaId: [Number, String],
});

const emit = defineEmits(['close']);

const form = useForm({
    provider_agenda_id: props.agendaId,
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    service_id: '',
    starts_at: '',
    time: '09:00',
    status: 'confirmed'
});

watch(() => props.agendaId, (newId) => {
    form.provider_agenda_id = newId;
});

const submit = () => {
    // Combinar data e hora para o backend
    const combinedDateTime = `${form.starts_at} ${form.time}:00`;
    
    form.transform(data => ({
        ...data,
        starts_at: combinedDateTime
    })).post(route('bookings.store'), {
        preserveScroll: true,
        onSuccess: () => {
            close();
            form.reset();
        },
    });
};

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :open="open" @close="close">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-black text-[#18181b]">Novo Agendamento</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Cadastro manual de cliente</p>
                </div>
                <button @click="close" class="p-2 rounded-xl hover:bg-gray-100 transition-all text-gray-400 hover:text-black">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Cliente -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Informações do Cliente</label>
                    <div class="relative group">
                        <User class="absolute left-4 top-3.5 h-4 w-4 text-gray-300 group-focus-within:text-black transition-colors" />
                        <input 
                            v-model="form.customer_name" 
                            type="text" 
                            placeholder="Nome completo"
                            class="w-full h-12 pl-11 pr-4 rounded-2xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-black/5 focus:border-black outline-none transition-all text-sm font-bold"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <input 
                            v-model="form.customer_email" 
                            type="email" 
                            placeholder="E-mail (opcional)"
                            class="w-full h-12 px-4 rounded-2xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-black/5 focus:border-black outline-none transition-all text-sm font-bold"
                        />
                    </div>
                    <div class="space-y-1.5">
                        <input 
                            v-model="form.customer_phone" 
                            type="text" 
                            placeholder="WhatsApp / Telefone"
                            class="w-full h-12 px-4 rounded-2xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-black/5 focus:border-black outline-none transition-all text-sm font-bold"
                        />
                    </div>
                </div>

                <!-- Serviço -->
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Serviço Desejado</label>
                    <div class="relative group">
                        <Scissors class="absolute left-4 top-3.5 h-4 w-4 text-gray-300 group-focus-within:text-black transition-colors" />
                        <select 
                            v-model="form.service_id"
                            class="w-full h-12 pl-11 pr-4 rounded-2xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-black/5 focus:border-black outline-none transition-all text-sm font-bold appearance-none"
                            required
                        >
                            <option value="" disabled>Selecione um serviço...</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">
                                {{ service.name }} — R$ {{ (service.price_cents / 100).toFixed(2).replace('.', ',') }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Data e Hora -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Data</label>
                        <div class="relative group">
                            <Calendar class="absolute left-4 top-3.5 h-4 w-4 text-gray-300 group-focus-within:text-black transition-colors" />
                            <input 
                                v-model="form.starts_at" 
                                type="date" 
                                class="w-full h-12 pl-11 pr-4 rounded-2xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-black/5 focus:border-black outline-none transition-all text-sm font-bold"
                                required
                            />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-1">Horário</label>
                        <div class="relative group">
                            <Clock class="absolute left-4 top-3.5 h-4 w-4 text-gray-300 group-focus-within:text-black transition-colors" />
                            <input 
                                v-model="form.time" 
                                type="time" 
                                class="w-full h-12 pl-11 pr-4 rounded-2xl border border-gray-100 bg-gray-50 focus:ring-2 focus:ring-black/5 focus:border-black outline-none transition-all text-sm font-bold"
                                required
                            />
                        </div>
                    </div>
                </div>

                <!-- Botão de Ação -->
                <div class="pt-4 flex gap-3">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="flex-1 h-14 bg-[#18181b] text-white rounded-[1.25rem] font-black text-sm flex items-center justify-center gap-2 hover:bg-black transition-all shadow-xl shadow-black/10 active:scale-95 disabled:opacity-50"
                    >
                        <Check v-if="!form.processing" class="h-4 w-4" />
                        {{ form.processing ? 'Agendando...' : 'Confirmar Agendamento' }}
                    </button>
                    <button 
                        type="button" 
                        @click="close"
                        class="px-6 h-14 rounded-[1.25rem] border border-gray-100 text-sm font-black text-gray-400 hover:bg-gray-50 transition-all active:scale-95"
                    >
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { 
    ChevronLeft, 
    Calendar, 
    Scissors, 
    CreditCard, 
    Palette, 
    Check,
    ChevronDown,
    Plus,
    X,
    DollarSign,
    Clock,
    ChevronRight,
    Circle
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    agenda: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);
const { auth } = usePage().props;

const currentStepId = ref('horarios');
const selectedServicesMockup = ref([]);

const steps = [
    { id: 'horarios', label: 'Horarios', icon: Clock },
    { id: 'servicos', label: 'Servicos', icon: Scissors },
    { id: 'pagamento', label: 'Pagamento', icon: CreditCard },
    { id: 'cores', label: 'Cores', icon: Palette },
    { id: 'confirmar', label: 'Confirmar', icon: Check },
];

const WEEKDAY_MAP_REVERSE = {
    1: 'seg',
    2: 'ter',
    3: 'qua',
    4: 'qui',
    5: 'sex',
    6: 'sab',
    0: 'dom'
};

const form = useForm({
    name: props.agenda?.name || '',
    professional: props.agenda?.provider_profile?.user?.name || '',
    weekdays: props.agenda?.availability_rules 
        ? [...new Set(props.agenda.availability_rules.map(r => WEEKDAY_MAP_REVERSE[r.weekday]))].filter(Boolean)
        : ['seg', 'ter', 'qua', 'qui', 'sex'],
    startTime: props.agenda?.availability_rules?.[0]?.starts_at?.substring(0, 5) || '08:00',
    endTime: props.agenda?.availability_rules?.[0]?.ends_at?.substring(0, 5) || '18:00',
    interval: props.agenda?.services?.[0]?.duration_minutes || 30,
    services: props.agenda?.services?.map(s => ({
        id: s.id,
        name: s.name,
        price: (s.price_cents / 100).toString().replace('.', ','),
        isFree: s.price_cents === 0
    })) || [],
    payment_requirement: props.agenda?.payment_requirement || 'none',
    primaryColor: props.agenda?.primary_color || '#18181b',
    secondaryColor: props.agenda?.secondary_color || '#27272a',
    slug: props.agenda?.slug || '',
    allowDocs: false,
    docsTitle: '',
    embed_width: props.agenda?.embed_width || 480,
    transparent_bg: props.agenda?.transparent_bg || false,
});

const THEMES = [
    { name: 'Escuro', primary: '#18181b', secondary: '#27272a' },
    { name: 'Azul', primary: '#3b82f6', secondary: '#eff6ff' },
    { name: 'Esmeralda', primary: '#10b981', secondary: '#ecfdf5' },
    { name: 'Rosa', primary: '#ed4b82', secondary: '#fff1f2' },
    { name: 'Laranja', primary: '#f97316', secondary: '#fff7ed' },
    { name: 'Indigo', primary: '#6366f1', secondary: '#eef2ff' },
];

const WEEKDAY_LABELS = {
    seg: 'Seg', ter: 'Ter', qua: 'Qua', qui: 'Qui', sex: 'Sex', sab: 'Sab', dom: 'Dom'
};

const PAYMENT_LABELS = {
    none: 'Sem pagamento antecipado',
    full: 'Pagamento integral',
    half: '50% de sinal'
};

const applyTheme = (theme) => {
    form.primaryColor = theme.primary;
    form.secondaryColor = theme.secondary;
};

const WEEKDAYS = [
    { id: 'seg', label: 'Seg' }, { id: 'ter', label: 'Ter' }, { id: 'qua', label: 'Qua' },
    { id: 'qui', label: 'Qui' }, { id: 'sex', label: 'Sex' }, { id: 'sab', label: 'Sab' },
    { id: 'dom', label: 'Dom' },
];

const INTERVALS = [15, 20, 30, 45, 60, 90];

const toggleWeekday = (day) => {
    const index = form.weekdays.indexOf(day);
    if (index > -1) {
        if (form.weekdays.length > 1) form.weekdays.splice(index, 1);
    } else {
        form.weekdays.push(day);
    }
};

const hours = Array.from({ length: 48 }, (_, i) => {
    const h = Math.floor(i / 2);
    const m = (i % 2) === 0 ? '00' : '30';
    return `${String(h).padStart(2, '0')}:${m}`;
});

const addService = () => {
    form.services.push({ id: Date.now(), name: '', price: '', isFree: false });
};

const removeService = (index) => {
    form.services.splice(index, 1);
};

const mockupStep = ref('services'); // 'services', 'date', 'time', 'confirmation', 'success'
const mockupDate = ref(18); // Dia padrão para teste
const mockupTime = ref(null);
const mockupFileName = ref(null);

const isMockupDateTimeReady = computed(() => mockupDate.value && mockupTime.value);

const handleMockupFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) mockupFileName.value = file.name;
};

const selectedMockupDateFormatted = computed(() => {
    return `${mockupDate.value} Fev, 2026`;
});

const toggleMockupService = (serviceId) => {
    const idx = selectedServicesMockup.value.indexOf(serviceId);
    if (idx > -1) selectedServicesMockup.value.splice(idx, 1);
    else selectedServicesMockup.value.push(serviceId);
};

const mockupTotal = computed(() => {
    const selected = form.services.filter(s => selectedServicesMockup.value.includes(s.id));
    const total = selected.reduce((acc, s) => {
        if (s.isFree) return acc;
        const p = parseFloat(String(s.price).replace(',', '.'));
        return acc + (isNaN(p) ? 0 : p);
    }, 0);
    return total === 0 ? 'Gratuito' : `R$ ${total.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
});

const isStepCompleted = (stepId) => {
    const stepOrder = ['horarios', 'servicos', 'pagamento', 'cores', 'confirmar'];
    return stepOrder.indexOf(stepId) < stepOrder.indexOf(currentStepId.value);
};

const nextStep = () => {
    const stepOrder = ['horarios', 'servicos', 'pagamento', 'cores', 'confirmar'];
    const idx = stepOrder.indexOf(currentStepId.value);
    if (idx < stepOrder.length - 1) currentStepId.value = stepOrder[idx + 1];
    else submit();
};

const prevStep = () => {
    const stepOrder = ['horarios', 'servicos', 'pagamento', 'cores', 'confirmar'];
    const idx = stepOrder.indexOf(currentStepId.value);
    if (idx > 0) currentStepId.value = stepOrder[idx - 1];
    else emit('close');
};

const submit = () => {
    if (!form.name) return;
    form.slug = form.name.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
    
    if (props.agenda) {
        form.put(route('provider.agendas.update', props.agenda.id), { 
            onSuccess: () => emit('close') 
        });
    } else {
        form.post(route('provider.agendas.store'), { 
            onSuccess: () => emit('close') 
        });
    }
};

const mockupSlots = computed(() => {
    const slots = [];
    let current = form.startTime;
    const end = form.endTime;

    const toMinutes = (time) => {
        const [h, m] = time.split(':').map(Number);
        return h * 60 + m;
    };

    let currentMin = toMinutes(current);
    const endMin = toMinutes(end);

    while (currentMin < endMin) {
        const h = Math.floor(currentMin / 60);
        const m = currentMin % 60;
        slots.push(`${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`);
        currentMin += form.interval;
    }

    return slots;
});
</script>

<template>
    <div class="p-6 bg-[#f9fafb] min-h-screen font-sans">
        <div class="max-w-[1000px] mx-auto">
            
            <!-- Compact Header -->
            <div class="flex items-center gap-4 mb-6">
                <button @click="emit('close')" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-400 hover:bg-gray-50 transition-all shadow-sm">
                    <ChevronLeft class="h-4 w-4" />
                </button>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[#18181b]">{{ agenda ? 'Editar Agenda' : 'Nova Agenda' }}</h1>
                    <p class="text-gray-400 text-sm mt-0.5">{{ agenda ? 'Altere os detalhes da sua agenda de atendimento.' : 'Configure os detalhes da nova agenda de atendimento.' }}</p>
                </div>
            </div>

            <!-- Compact Stepper -->
            <div class="flex items-center gap-2 mb-8 overflow-x-auto no-scrollbar py-1">
                <template v-for="(step, index) in steps" :key="step.id">
                    <div @click="currentStepId = step.id" :class="cn('flex items-center gap-2 px-4 py-1.5 rounded-full transition-all cursor-pointer whitespace-nowrap', currentStepId === step.id ? 'bg-black text-white shadow-md' : isStepCompleted(step.id) ? 'bg-[#10b981]/10 text-[#10b981]' : 'bg-[#f4f4f5] text-gray-400 hover:bg-gray-200')">
                        <Check v-if="isStepCompleted(step.id)" class="h-3.5 w-3.5" />
                        <component v-else :is="step.icon" class="h-3.5 w-3.5" />
                        <span class="text-xs font-bold">{{ step.label }}</span>
                    </div>
                    <div v-if="index < steps.length - 1" class="h-[1px] w-6 bg-gray-200 flex-shrink-0"></div>
                </template>
            </div>

            <!-- Main Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-8 items-start">
                
                <div class="flex flex-col gap-6">
                    <!-- Setup Form Card -->
                    <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm min-h-[400px]">
                        <!-- STEP components remain same as before but encapsulated for clarity -->
                        <div v-if="currentStepId === 'horarios'" class="space-y-6">
                            <div><h2 class="text-lg font-bold text-[#18181b]">Informacoes e Horarios</h2><p class="text-gray-400 text-sm">Defina o nome, profissional e os horarios de atendimento.</p></div>
                            <div class="space-y-5 max-w-lg">
                                <div class="space-y-1.5"><label class="text-xs font-bold text-[#18181b]">Nome da agenda</label><input v-model="form.name" type="text" placeholder="Ex: Horario Padrao" class="w-full h-11 px-4 rounded-lg border-gray-200 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none transition-all text-sm" /></div>
                                <div class="space-y-1.5"><label class="text-xs font-bold text-[#18181b]">Profissional responsavel</label><input v-model="form.professional" type="text" placeholder="Ex: Ana Costa" class="w-full h-11 px-4 rounded-lg border-gray-200 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none transition-all text-sm" /></div>
                                <div class="space-y-2.5"><label class="text-xs font-bold text-[#18181b]">Dias de atendimento</label><div class="flex flex-wrap gap-2"><button v-for="day in WEEKDAYS" :key="day.id" @click="toggleWeekday(day.id)" :class="cn('h-10 w-12 flex items-center justify-center rounded-lg font-bold text-xs border transition-all', form.weekdays.includes(day.id) ? 'bg-[#18181b] text-white border-[#18181b]' : 'bg-white text-gray-400 border-gray-100 hover:bg-gray-50')">{{ day.label }}</button></div></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1.5"><label class="text-xs font-bold text-[#18181b]">Inicio</label><div class="relative group"><select v-model="form.startTime" class="w-full h-11 pl-4 pr-10 appearance-none rounded-lg border-gray-200 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none transition-all text-sm"><option v-for="t in hours" :key="t" :value="t">{{ t }}</option></select><ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-400 pointer-events-none group-hover:text-[#18181b]" /></div></div>
                                    <div class="space-y-1.5"><label class="text-xs font-bold text-[#18181b]">Termino</label><div class="relative group"><select v-model="form.endTime" class="w-full h-11 pl-4 pr-10 appearance-none rounded-lg border-gray-200 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none transition-all text-sm"><option v-for="t in hours" :key="t" :value="t">{{ t }}</option></select><ChevronDown class="absolute right-3 top-3.5 h-4 w-4 text-gray-400 pointer-events-none group-hover:text-[#18181b]" /></div></div>
                                </div>
                                <div class="space-y-2.5"><label class="text-xs font-bold text-[#18181b]">Intervalo entre atendimentos</label><div class="flex flex-wrap gap-2"><button v-for="int in INTERVALS" :key="int" @click="form.interval = int" :class="cn('h-10 px-4 flex items-center justify-center rounded-lg font-bold text-xs border transition-all', form.interval === int ? 'bg-[#18181b] text-white border-[#18181b]' : 'bg-white text-gray-400 border-gray-100 hover:bg-gray-50')">{{ int }}min</button></div></div>
                                
                                <div class="pt-4 mt-4 border-t border-gray-100">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-xs font-bold text-[#18181b]">Solicitar Documento</h4>
                                            <p class="text-[10px] text-gray-400">Permitir que o cliente suba um documento no agendamento.</p>
                                        </div>
                                        <button @click="form.allowDocs = !form.allowDocs" :class="cn('w-10 h-5 rounded-full transition-all relative', form.allowDocs ? 'bg-[#10b981]' : 'bg-gray-200')">
                                            <div :class="cn('absolute top-1 w-3 h-3 rounded-full bg-white transition-all', form.allowDocs ? 'left-6' : 'left-1')"></div>
                                        </button>
                                    </div>
                                    <div v-if="form.allowDocs" class="space-y-1.5 animate-in slide-in-from-top-2 duration-300">
                                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Titulo do documento solicitado</label>
                                        <input v-model="form.docsTitle" type="text" placeholder="Ex: Foto do RG, Comprovante..." class="w-full h-11 px-4 rounded-lg border-gray-200 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none transition-all text-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="currentStepId === 'servicos'" class="space-y-6">
                            <div><h2 class="text-lg font-bold text-[#18181b]">Serviços Oferecidos</h2><p class="text-gray-400 text-sm">Adicione os serviços disponíveis nesta agenda e seus respectivos valores.</p></div>
                            <div class="space-y-4">
                                <div v-for="(service, index) in form.services" :key="service.id" class="p-6 rounded-xl border border-gray-100 bg-white relative transition-all group">
                                    <div class="flex justify-between items-center mb-6"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">SERVICO {{ index + 1 }}</span><button @click="removeService(index)" class="text-gray-300 hover:text-red-500 transition-colors"><X class="h-4 w-4" /></button></div>
                                    <div class="space-y-5">
                                        <div class="space-y-2"><label class="text-xs font-bold text-[#18181b]">Nome do serviço</label><input v-model="service.name" type="text" placeholder="ex: Corte Masculino" class="w-full h-11 px-4 rounded-lg border border-gray-100 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none text-sm font-medium" /></div>
                                        <div class="space-y-2"><label class="text-xs font-bold text-[#18181b]">Valor (R$)</label><div class="flex items-center gap-3"><div class="relative flex-1"><input v-model="service.price" type="text" :disabled="service.isFree" :placeholder="service.isFree ? 'R$ Gratuito' : '0,00'" class="w-full h-11 px-4 rounded-lg border border-gray-100 bg-[#f9fafb] focus:ring-1 focus:ring-black outline-none text-sm font-medium disabled:opacity-50" /></div><button @click="service.isFree = !service.isFree; if(service.isFree) service.price = ''" :class="cn('flex items-center gap-2 px-4 h-11 rounded-lg border transition-all font-bold text-xs', service.isFree ? 'bg-[#10b981]/10 text-[#10b981] border-[#10b981]' : 'bg-white border-gray-100 text-gray-400')"><Check v-if="service.isFree" class="h-3.5 w-3.5" />Gratuito</button></div></div>
                                    </div>
                                </div>
                                <button @click="addService" class="w-full h-16 border-2 border-dashed border-gray-100 rounded-lg flex items-center justify-center gap-2 text-gray-400 hover:border-gray-200 hover:text-gray-500 transition-all font-bold text-sm"><Plus class="h-4 w-4" />Adicionar servico</button>
                                <p v-if="form.services.length === 0" class="text-center text-gray-400 text-xs mt-4">Adicione pelo menos um serviço para continuar.</p>
                            </div>
                        </div>

                        <div v-if="currentStepId === 'pagamento'" class="space-y-6">
                            <div><h2 class="text-lg font-bold text-[#18181b]">Pagamento para Confirmacao</h2><p class="text-gray-400 text-sm">Escolha se o cliente precisa pagar para confirmar o agendamento.</p></div>
                            <div class="space-y-3 max-w-lg">
                                <div v-for="opt in [{ id: 'none', title: 'Sem pagamento antecipado', desc: 'O cliente agenda sem necessidade de pagamento. O pagamento e feito presencialmente.' },{ id: 'full', title: 'Pagamento integral', desc: 'O cliente paga 100% do valor do servico para confirmar o agendamento.' },{ id: 'half', title: '50% de sinal', desc: 'O cliente paga 50% do valor como sinal para confirmar. O restante e pago presencialmente.' }]" :key="opt.id" @click="form.payment_requirement = opt.id" :class="cn('flex items-center gap-4 p-5 rounded-xl border transition-all cursor-pointer group', form.payment_requirement === opt.id ? 'border-[#18181b] bg-white ring-1 ring-[#18181b]' : 'border-gray-100 hover:bg-gray-50')"><div :class="cn('h-5 w-5 rounded-full border-2 flex items-center justify-center transition-all', form.payment_requirement === opt.id ? 'bg-[#18181b] border-[#18181b]' : 'border-gray-200 group-hover:border-gray-300')"><Check v-if="form.payment_requirement === opt.id" class="h-3 w-3 text-white" /></div><div><h3 class="text-sm font-bold text-[#18181b]">{{ opt.title }}</h3><p class="text-gray-400 text-xs mt-0.5 leading-relaxed">{{ opt.desc }}</p></div></div>
                            </div>
                        </div>

                        <div v-if="currentStepId === 'cores'" class="space-y-8">
                            <div><h2 class="text-lg font-bold text-[#18181b]">Cores do Agendamento</h2><p class="text-gray-400 text-sm">Escolha as cores que o cliente vera na pagina de agendamento.</p></div>
                            <div class="space-y-4"><h3 class="text-xs font-bold text-[#18181b] uppercase tracking-wider">Temas rápidos</h3><div class="grid grid-cols-2 sm:grid-cols-3 gap-3"><button v-for="theme in THEMES" :key="theme.name" @click="applyTheme(theme)" :class="cn('flex items-center gap-3 p-3 rounded-lg border transition-all', form.primaryColor === theme.primary ? 'border-black bg-white shadow-sm ring-1 ring-black' : 'border-gray-100 bg-white hover:bg-gray-50')"><div class="h-5 w-5 rounded-full" :style="{ backgroundColor: theme.primary }"></div><span class="text-sm font-bold text-[#18181b]">{{ theme.name }}</span></button></div></div>
                            <div class="grid grid-cols-2 gap-6">
                                <div class="space-y-3"><label class="text-xs font-bold text-[#18181b]">Cor principal</label><div class="flex items-center gap-3 h-11 px-4 rounded-lg border border-gray-100 bg-[#f9fafb]"><div class="h-6 w-6 rounded border border-gray-200" :style="{ backgroundColor: form.primaryColor }"></div><input v-model="form.primaryColor" type="text" class="bg-transparent border-none outline-none text-sm font-semibold text-gray-500 w-full" /></div></div>
                                <div class="space-y-3"><label class="text-xs font-bold text-[#18181b]">Cor secundaria</label><div class="flex items-center gap-3 h-11 px-4 rounded-lg border border-gray-100 bg-[#f9fafb]"><div class="h-6 w-6 rounded border border-gray-200" :style="{ backgroundColor: form.secondaryColor }"></div><input v-model="form.secondaryColor" type="text" class="bg-transparent border-none outline-none text-sm font-semibold text-gray-500 w-full" /></div></div>
                            </div>

                            <div class="pt-8 border-t border-gray-100 space-y-6">
                                <h3 class="text-xs font-bold text-[#18181b] uppercase tracking-wider">Configurações de Incorporamento (Embed)</h3>
                                <div class="grid grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <label class="text-xs font-bold text-[#18181b]">Largura Padrão (px)</label>
                                        <div class="flex items-center gap-3 h-11 px-4 rounded-lg border border-gray-100 bg-[#f9fafb]">
                                            <input v-model.number="form.embed_width" type="number" step="10" min="300" max="1200" class="bg-transparent border-none outline-none text-sm font-semibold text-[#18181b] w-full" />
                                        </div>
                                        <p class="text-[10px] text-gray-400">Largura em pixels que a agenda ocupará no seu site.</p>
                                    </div>
                                    <div class="space-y-3">
                                        <label class="text-xs font-bold text-[#18181b]">Transparência</label>
                                        <button @click="form.transparent_bg = !form.transparent_bg" :class="cn('w-full h-11 px-4 rounded-lg border transition-all flex items-center justify-between font-bold text-xs', form.transparent_bg ? 'bg-[#10b981]/10 text-[#10b981] border-[#10b981]' : 'bg-white border-gray-100 text-gray-400')">
                                            <span>Remover fundo branco</span>
                                            <div :class="cn('h-5 w-5 rounded-full flex items-center justify-center transition-all', form.transparent_bg ? 'bg-[#10b981]' : 'bg-gray-200')">
                                                <Check v-if="form.transparent_bg" class="h-3 w-3 text-white" />
                                            </div>
                                        </button>
                                        <p class="text-[10px] text-gray-400">Útil para sites com fundo escuro ou colorido.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="currentStepId === 'confirmar'" class="space-y-6">
                            <div><h2 class="text-lg font-bold text-[#18181b]">Resumo da Agenda</h2><p class="text-gray-400 text-sm">Revise as configuracoes antes de criar a agenda.</p></div>
                            <div class="space-y-4 overflow-y-auto max-h-[400px] pr-2 no-scrollbar">
                                <div class="p-4 rounded-xl border border-gray-100 bg-white"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">INFORMACOES</span><div class="flex justify-between py-1"><span class="text-xs text-gray-500 font-medium">Nome</span><span class="text-xs font-bold text-[#18181b]">{{ form.name || '---' }}</span></div><div class="flex justify-between py-1"><span class="text-xs text-gray-500 font-medium">Profissional</span><span class="text-xs font-bold text-[#18181b]">{{ form.professional || '---' }}</span></div></div>
                                <div class="p-4 rounded-xl border border-gray-100 bg-white"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">SERVICOS ({{ form.services.length }})</span><div v-for="s in form.services" :key="s.id" class="flex justify-between py-1"><span class="text-xs text-[#18181b] font-bold">{{ s.name || '---' }}</span><span :class="cn('text-xs font-bold', s.isFree ? 'text-[#10b981]' : 'text-[#18181b]')">{{ s.isFree ? 'Gratuito' : `R$ ${s.price || '0,00'}` }}</span></div></div>
                                <div class="p-4 rounded-xl border border-gray-100 bg-white"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">HORARIO</span><div class="flex justify-between py-1"><span class="text-xs text-gray-500 font-medium">Dias</span><span class="text-xs font-bold text-[#18181b]">{{ form.weekdays.map(d => WEEKDAY_LABELS[d]).join(', ') }}</span></div><div class="flex justify-between py-1"><span class="text-xs text-gray-500 font-medium">Horario</span><span class="text-xs font-bold text-[#18181b]">{{ form.startTime }} - {{ form.endTime }}</span></div></div>
                                 <div class="p-4 rounded-xl border border-gray-100 bg-white"><span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">PAGAMENTO</span><span class="text-xs font-bold text-[#18181b]">{{ PAYMENT_LABELS[form.payment_requirement] }}</span></div>
                                 <div v-if="form.allowDocs" class="p-4 rounded-xl border border-gray-100 bg-white shadow-sm ring-1 ring-[#10b981]/10">
                                     <span class="text-[10px] font-bold text-[#10b981] uppercase tracking-widest block mb-2">DOCUMENTO SOLICITADO</span>
                                     <span class="text-xs font-bold text-[#18181b]">{{ form.docsTitle || 'Sem título' }}</span>
                                 </div>
                            </div>
                        </div>
                    </div>

                    <!-- External Actions -->
                    <div class="flex items-center justify-between">
                        <button @click="prevStep" class="flex items-center gap-2 h-10 px-6 bg-white border border-gray-100 rounded-lg text-xs font-bold text-[#18181b] hover:bg-gray-50 transition-all shadow-sm"><ChevronLeft class="h-4 w-4" />Voltar</button>
                        <button v-if="currentStepId !== 'confirmar'" @click="nextStep" class="flex items-center justify-center gap-2 h-10 px-8 bg-black text-white rounded-lg text-xs font-bold hover:bg-black/90 transition-all shadow-sm group">Proximo<ChevronRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" /></button>
                        <button v-else @click="submit" class="flex items-center justify-center gap-2 h-11 px-10 bg-black text-white rounded-xl text-sm font-bold hover:bg-black/90 transition-all shadow-xl group">{{ agenda ? 'Salvar Alterações' : 'Criar Agenda' }}<Check class="h-4.5 w-4.5 ml-1 transition-transform group-hover:scale-110" /></button>
                    </div>
                </div>

                <!-- Preview Column -->
                <div class="sticky top-6">
                    <div class="flex items-center gap-2 mb-4 ml-1">
                        <div class="h-2 w-2 rounded-full bg-[#10b981]"></div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pre-visualizacao do cliente</span>
                    </div>

                    <!-- Interactive Client Mockup -->
                    <div class="rounded-[2.5rem] overflow-hidden flex flex-col transition-all" 
                        :class="form.transparent_bg ? 'bg-transparent border-none' : 'bg-white border border-gray-100 shadow-2xl'"
                        :style="{ maxWidth: form.embed_width + 'px' }"
                    >
                        <!-- Preview Header -->
                        <div class="px-8 py-8 text-white flex items-center gap-5 transition-all duration-500" :style="{ backgroundColor: form.primaryColor }">
                            <div class="h-12 w-12 rounded-full bg-white/10 flex items-center justify-center text-xl font-bold text-white shadow-inner">
                                {{ form.name ? form.name.charAt(0).toUpperCase() : 'B' }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold leading-tight">{{ form.name || 'Minha Agenda' }}</h3>
                                <p class="text-white/40 text-[12px] font-medium">{{ form.professional || 'Profissional' }}</p>
                            </div>
                        </div>

                        <!-- Preview Content -->
                        <div class="flex-1 p-8 flex flex-col relative">
                            <!-- Progress Stepper (Updated to 4 steps) -->
                            <div class="flex items-center gap-2 mb-10">
                                <div :class="cn('h-8 w-8 rounded-full flex items-center justify-center text-[11px] font-bold transition-all', mockupStep !== 'services' ? 'text-white' : 'bg-black text-white')" :style="mockupStep !== 'services' ? { backgroundColor: form.primaryColor } : {}">
                                    <Check v-if="mockupStep !== 'services'" class="h-4 w-4 text-white" />
                                    <span v-else>1</span>
                                </div>
                                <div class="h-[1px] flex-1 bg-gray-100"></div>
                                <div :class="cn('h-8 w-8 rounded-full flex items-center justify-center text-[11px] font-bold transition-all', ['date', 'time', 'confirmation'].includes(mockupStep) ? '' : 'border border-gray-100 text-gray-200')" :style="['time', 'confirmation'].includes(mockupStep) ? { backgroundColor: form.primaryColor, color: 'white' } : mockupStep === 'date' ? { backgroundColor: '#000', color: 'white' } : {}">
                                    <Check v-if="['time', 'confirmation'].includes(mockupStep)" class="h-4 w-4 text-white" />
                                    <span v-else>2</span>
                                </div>
                                <div class="h-[1px] flex-1 bg-gray-100"></div>
                                <div :class="cn('h-8 w-8 rounded-full flex items-center justify-center text-[11px] font-bold transition-all', ['time', 'confirmation'].includes(mockupStep) ? '' : 'border border-gray-100 text-gray-200')" :style="mockupStep === 'confirmation' ? { backgroundColor: form.primaryColor, color: 'white' } : mockupStep === 'time' ? { backgroundColor: '#000', color: 'white' } : {}">
                                    <Check v-if="mockupStep === 'confirmation'" class="h-4 w-4 text-white" />
                                    <span v-else>3</span>
                                </div>
                                <div class="h-[1px] flex-1 bg-gray-100"></div>
                                <div :class="cn('h-8 w-8 rounded-full flex items-center justify-center text-[11px] font-bold transition-all', mockupStep === 'confirmation' ? 'text-white' : 'border border-gray-100 text-gray-200')" :style="mockupStep === 'confirmation' ? { backgroundColor: form.primaryColor } : {}">4</div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 ml-2 tracking-widest whitespace-nowrap">
                                    {{ mockupStep === 'services' ? 'Serviços' : mockupStep === 'date' ? 'Data' : mockupStep === 'time' ? 'Horário' : 'Dados' }}
                                </span>
                            </div>

                            <!-- STEP 1: SERVICES -->
                            <div v-if="mockupStep === 'services'" class="flex-1 flex flex-col">
                                <div class="flex items-center gap-2 mb-6">
                                    <Scissors class="h-3.5 w-3.5 text-gray-400" />
                                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Selecione os servicos desejados</span>
                                </div>

                                <div class="flex-1">
                                    <div v-if="form.services.length === 0" class="h-full border border-dashed border-gray-200 rounded-[2rem] flex flex-col items-center justify-center p-8 text-center bg-gray-50/50">
                                        <Scissors class="h-8 w-8 text-gray-200 mb-4" />
                                        <p class="text-[12px] font-bold text-gray-400">Nenhum servico adicionado ainda.</p>
                                    </div>
                                    <div v-else class="space-y-3">
                                        <div 
                                            v-for="s in form.services.filter(s => s.name)" 
                                            :key="s.id" 
                                            @click="toggleMockupService(s.id)"
                                            :class="cn(
                                                'p-4 rounded-xl border transition-all cursor-pointer flex justify-between items-center shadow-sm',
                                                selectedServicesMockup.includes(s.id) 
                                                    ? 'bg-[#f4f4f5]' 
                                                    : 'border-gray-100 bg-white hover:bg-gray-50'
                                            )"
                                            :style="selectedServicesMockup.includes(s.id) ? { borderColor: form.primaryColor, boxShadow: `0 0 0 1px ${form.primaryColor}` } : {}"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div :class="cn('h-5 w-5 rounded border flex items-center justify-center transition-all', selectedServicesMockup.includes(s.id) ? 'border-transparent' : 'border-gray-200')" :style="selectedServicesMockup.includes(s.id) ? { backgroundColor: form.primaryColor } : {}">
                                                    <Check v-if="selectedServicesMockup.includes(s.id)" class="h-3 w-3 text-white" />
                                                </div>
                                                <span class="text-xs font-bold text-gray-700">{{ s.name }}</span>
                                            </div>
                                            <span :class="cn('text-xs font-bold', s.isFree ? 'text-[#10b981]' : 'text-gray-700')">
                                                {{ s.isFree ? 'Gratuito' : `R$ ${s.price || '0,00'}` }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="selectedServicesMockup.length > 0" class="mt-6 pt-6 border-t border-gray-100 flex flex-col gap-4">
                                    <div class="flex justify-between items-center px-2">
                                        <span class="text-[11px] font-bold text-gray-400">{{ selectedServicesMockup.length }} selecionado(s)</span>
                                        <span class="text-xs font-extrabold" :class="mockupTotal === 'Gratuito' ? 'text-[#10b981]' : 'text-black'">{{ mockupTotal }}</span>
                                    </div>
                                    <button @click="mockupStep = 'date'" class="w-full h-12 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 hover:opacity-90" :style="{ backgroundColor: form.primaryColor }">
                                        Continuar
                                    </button>
                                </div>
                                <div v-else class="mt-8 flex justify-end pr-2 opacity-30">
                                    <span class="text-[8px] text-gray-500 font-bold uppercase tracking-widest">Powered by zenith</span>
                                </div>
                            </div>

                            <!-- STEP 2: DATE -->
                            <div v-if="mockupStep === 'date'" class="flex-1 flex flex-col">
                                <div class="flex items-center gap-2 mb-6 text-gray-400">
                                    <button @click="mockupStep = 'services'" class="hover:text-black transition-colors"><ChevronLeft class="h-4 w-4" /></button>
                                    <Calendar class="h-4 w-4" />
                                    <span class="text-[10px] uppercase font-bold tracking-wider">Escolha a data</span>
                                </div>

                                <div class="bg-[#f9fafb] rounded-[2rem] p-6 mb-6">
                                    <div class="flex justify-between items-center mb-6 px-2">
                                        <span class="text-sm font-bold text-gray-800">Fevereiro 2026</span>
                                        <div class="flex gap-4 text-gray-400">
                                            <ChevronLeft class="h-4 w-4 opacity-30 cursor-not-allowed" />
                                            <ChevronRight class="h-4 w-4 cursor-pointer hover:text-black" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-7 gap-2 text-center mb-4">
                                        <span v-for="d in ['D','S','T','Q','Q','S','S']" :key="d" class="text-[10px] font-bold text-gray-300">{{ d }}</span>
                                    </div>
                                    <div class="grid grid-cols-7 gap-2">
                                        <div 
                                            v-for="n in 28" :key="n" 
                                            @click="n >= 18 ? mockupDate = n : null"
                                            :class="cn(
                                                'h-9 flex items-center justify-center text-[11px] font-bold rounded-xl transition-all', 
                                                mockupDate === n ? 'text-white shadow-md' : n < 18 ? 'text-gray-200' : 'text-gray-600 hover:bg-gray-100 cursor-pointer'
                                            )"
                                            :style="mockupDate === n ? { backgroundColor: form.primaryColor } : {}"
                                        >
                                            {{ n }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <button @click="mockupStep = 'time'" class="w-full h-12 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 hover:opacity-90" :style="{ backgroundColor: form.primaryColor }">
                                        Continuar
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 3: TIME -->
                            <div v-if="mockupStep === 'time'" class="flex-1 flex flex-col">
                                <div class="flex items-center gap-2 mb-6 text-gray-400">
                                    <button @click="mockupStep = 'date'" class="hover:text-black transition-colors"><ChevronLeft class="h-4 w-4" /></button>
                                    <Clock class="h-4 w-4" />
                                    <span class="text-[10px] uppercase font-bold tracking-wider">Escolha o horário</span>
                                    <span class="ml-auto text-[10px] font-bold text-gray-400">{{ selectedMockupDateFormatted }}</span>
                                </div>

                                <!-- Mock Time Slots (Scrollable) -->
                                <div class="space-y-3">
                                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-2 block">Horários disponíveis</span>
                                    <div class="grid grid-cols-3 gap-3 max-h-[180px] overflow-y-auto pr-1 no-scrollbar">
                                        <button 
                                            v-for="time in mockupSlots" 
                                            :key="time" 
                                            @click="mockupTime = time"
                                            :class="cn(
                                                'h-11 border rounded-xl text-xs font-bold transition-all',
                                                mockupTime === time ? 'text-white border-transparent' : 'border-gray-100 text-gray-700 hover:border-black'
                                            )"
                                            :style="mockupTime === time ? { backgroundColor: form.primaryColor } : {}"
                                        >
                                            {{ time }}
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-auto pt-6 border-t border-gray-100">
                                    <button 
                                        @click="mockupTime ? mockupStep = 'confirmation' : null"
                                        :class="cn(
                                            'w-full h-12 rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95',
                                            mockupTime ? 'text-white hover:opacity-90' : 'bg-gray-200 text-white cursor-not-allowed'
                                        )"
                                        :style="mockupTime ? { backgroundColor: form.primaryColor } : {}"
                                    >
                                        Continuar
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 4: CONFIRMATION (Precisely matching Image 2) -->
                            <div v-if="mockupStep === 'confirmation'" class="flex-1 flex flex-col">
                                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-sm mb-6">
                                    <div class="flex items-center gap-3 mb-6 text-gray-500">
                                        <div class="h-4 w-4"><Calendar class="h-4 w-4" /></div>
                                        <span class="text-xs font-bold text-[#18181b]">Detalhes do agendamento</span>
                                    </div>

                                    <div class="space-y-4 mb-6 grid gap-6" :class="{ 'grid-cols-2': form.embed_width > 550 }">
                                        <div class="bg-white p-6 rounded-2xl border border-gray-100">
                                            <div class="flex items-center gap-3 mb-6 text-gray-400">
                                                <Calendar class="h-4 w-4" />
                                                <span class="text-[11px] font-bold text-gray-700">Detalhes</span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">SERVICOS</span>
                                                <div v-for="s in form.services.filter(s => selectedServicesMockup.includes(s.id))" :key="s.id" class="flex justify-between items-center mb-1">
                                                    <span class="text-xs font-medium text-gray-600 truncate">{{ s.name }}</span>
                                                    <span class="text-xs font-bold text-[#18181b]">{{ s.isFree ? 'Gratuito' : `R$ ${s.price || '0,00'}` }}</span>
                                                </div>
                                            </div>

                                            <div class="h-[1px] bg-gray-50 my-4"></div>

                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-bold text-[#18181b]">Total</span>
                                                <span class="text-sm font-bold" :style="{ color: '#10b981' }">{{ mockupTotal }}</span>
                                            </div>
                                        </div>

                                        <div class="space-y-4">
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-400 font-medium">Data</span>
                                                <span class="font-bold text-[#18181b]">{{ selectedMockupDateFormatted }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-400 font-medium">Horario</span>
                                                <span class="font-bold text-[#18181b]">{{ mockupTime }}</span>
                                            </div>
                                            <div class="h-[1px] bg-gray-50 my-2"></div>
                                            <div class="space-y-3">
                                                <div class="space-y-1"><label class="text-[9px] font-bold text-gray-400 uppercase">Nome</label><div class="h-9 rounded-lg bg-gray-50"></div></div>
                                                <div class="space-y-1"><label class="text-[9px] font-bold text-gray-400 uppercase">WhatsApp</label><div class="h-9 rounded-lg bg-gray-50"></div></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mock Document Upload (Functional) -->
                                    <div v-if="form.allowDocs" class="mt-6 pt-6 border-t border-gray-50">
                                        <div class="flex items-center gap-2 mb-3">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ form.docsTitle || 'Documento' }}</span>
                                            <span class="text-[9px] font-medium text-gray-300 uppercase">(Opcional)</span>
                                        </div>
                                        
                                        <input type="file" id="mockup-file-upload" class="hidden" @change="handleMockupFileUpload" />
                                        
                                        <label for="mockup-file-upload" :class="cn(
                                            'w-full min-h-[56px] border-2 border-dashed rounded-xl flex flex-col items-center justify-center cursor-pointer transition-all p-3',
                                            mockupFileName ? 'bg-[#10b981]/5 border-[#10b981]/30' : 'bg-gray-50/50 border-gray-100 hover:border-gray-200'
                                        )">
                                            <div v-if="!mockupFileName" class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-full bg-white flex items-center justify-center shadow-sm">
                                                    <Plus class="h-3 w-3 text-gray-400" />
                                                </div>
                                                <span class="text-[11px] font-bold text-gray-400">Clique para subir o arquivo</span>
                                            </div>
                                            <div v-else class="flex items-center justify-between w-full gap-3 animate-in fade-in slide-in-from-bottom-1 duration-300">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <div class="h-7 w-7 rounded-lg bg-[#10b981] flex items-center justify-center flex-shrink-0">
                                                        <Check class="h-3.5 w-3.5 text-white" />
                                                    </div>
                                                    <div class="flex flex-col truncate">
                                                        <span class="text-[10px] font-bold text-[#18181b] truncate">{{ mockupFileName }}</span>
                                                        <span class="text-[8px] font-bold text-[#10b981] uppercase">Arquivo subido</span>
                                                    </div>
                                                </div>
                                                <button @click.prevent="mockupFileName = null" class="h-6 w-6 rounded-md hover:bg-red-50 flex items-center justify-center text-gray-300 hover:text-red-500 transition-all">
                                                    <X class="h-3 w-3" />
                                                </button>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-auto space-y-4">
                                    <button @click="mockupStep = 'success'" class="w-full h-12 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 hover:opacity-90" :style="{ backgroundColor: form.primaryColor }">
                                        Confirmar Agendamento
                                    </button>
                                    <div class="flex justify-between items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest px-2">
                                        <button @click="mockupStep = 'time'" class="hover:text-black">Voltar</button>
                                        <span>Powered by zenith</span>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 5: SUCCESS SCREEN -->
                            <div v-if="mockupStep === 'success'" class="flex-1 flex flex-col items-center justify-center text-center p-6 animate-in fade-in zoom-in duration-500">
                                <div class="h-20 w-20 rounded-full flex items-center justify-center mb-8 shadow-lg" :style="{ backgroundColor: form.primaryColor + '15' }">
                                    <Check class="h-10 w-10" :style="{ color: form.primaryColor }" />
                                </div>
                                <h3 class="text-xl font-bold text-[#18181b] mb-3">Tudo pronto!</h3>
                                <p class="text-[13px] text-gray-400 leading-relaxed mb-10 px-4">
                                    Seu agendamento para <strong>{{ selectedMockupDateFormatted }}</strong> às <strong>{{ mockupTime }}</strong> foi realizado com sucesso.
                                </p>
                                
                                <div v-if="form.payment_requirement !== 'none'" class="w-full p-5 rounded-2xl bg-gray-50 border border-gray-100 mb-10 text-left">
                                    <div class="flex items-center gap-3 mb-2">
                                        <CreditCard class="h-4 w-4 text-blue-500" />
                                        <span class="text-xs font-bold text-[#18181b]">Pagamento Pendente</span>
                                    </div>
                                    <p class="text-[11px] text-gray-500">Um link de pagamento será enviado para o seu e-mail/whatsapp para confirmar sua reserva.</p>
                                </div>

                                <button @click="mockupStep = 'services'; mockupTime = null;" class="px-8 py-3 rounded-xl border border-gray-100 text-xs font-bold text-gray-400 hover:text-black hover:border-gray-200 transition-all uppercase tracking-widest">
                                    Recomeçar teste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

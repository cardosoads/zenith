<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Modal from '@/Components/UI/Modal.vue';
import FeedbackModal from '@/Components/UI/FeedbackModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { applyMoneyMask, centsToMaskedBrl, formatCentsToBrl, maskedBrlToCents } from '@/utils/currency';
import { 
    Pencil, 
    Plus, 
    Settings2, 
    Trash2, 
    Search, 
    MoreHorizontal, 
    Clock, 
    DollarSign, 
    Scissors,
    ToggleLeft,
    ToggleRight,
    X,
    Tag
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    services: {
        type: Array,
        default: () => [],
    },
    selectedAgendaId: {
        type: Number,
        default: null,
    },
    agendas: {
        type: Array,
        default: () => [],
    }
});

const search = ref("");
const selectedCategory = ref("Todos");
const openMenu = ref(null);
const categoryInput = ref("");

const uniqueCategories = computed(() => {
    const cats = [];
    props.services.forEach(s => {
        if (s.categories && Array.isArray(s.categories)) {
            s.categories.forEach(c => cats.push(c.trim()));
        }
    });
    
    const unique = [...new Set(cats)].sort();
    if (unique.length === 0) return ["Todos"];
    return ["Todos", ...unique];
});

const filteredServices = computed(() => {
    return props.services.filter((service) => {
        const matchSearch = service.name.toLowerCase().includes(search.value.toLowerCase());
        const serviceCats = service.categories || [];
        const matchCategory = selectedCategory.value === "Todos" || serviceCats.includes(selectedCategory.value);
        return matchSearch && matchCategory;
    });
});

const createForm = useForm({
    provider_agenda_id: props.selectedAgendaId,
    name: '',
    categories: [],
    description: '',
    duration_minutes: 60,
    break_minutes: 0,
    price_display: '0,00',
    is_active: true,
});

const createModalOpen = ref(false);
const editModalOpen = ref(false);
const editingServiceId = ref(null);
const deleteConfirmOpen = ref(false);
const serviceToDelete = ref(null);

const editForm = useForm({
    provider_agenda_id: props.selectedAgendaId,
    name: '',
    categories: [],
    description: '',
    duration_minutes: 60,
    break_minutes: 0,
    price_display: '0,00',
    is_active: true,
});

const addCategory = (formType) => {
    const val = categoryInput.value.trim();
    if (!val) return;
    
    const form = formType === 'create' ? createForm : editForm;
    if (!form.categories.includes(val)) {
        form.categories.push(val);
    }
    categoryInput.value = "";
};

const removeCategory = (formType, index) => {
    const form = formType === 'create' ? createForm : editForm;
    form.categories.splice(index, 1);
};

const submitCreate = () => {
    createForm
        .transform((data) => ({
            ...data,
            provider_agenda_id: Number(data.provider_agenda_id),
            duration_minutes: Number(data.duration_minutes),
            break_minutes: Number(data.break_minutes),
            price_cents: maskedBrlToCents(data.price_display),
        }))
        .post(route('services.store'), {
            preserveScroll: true,
            onSuccess: () => {
                createForm.reset();
                createForm.price_display = '0,00';
                createModalOpen.value = false;
            },
        });
};

const openEditModal = (service) => {
    editingServiceId.value = service.id;
    editForm.provider_agenda_id = props.selectedAgendaId;
    editForm.name = service.name;
    editForm.categories = Array.isArray(service.categories) ? [...service.categories] : [];
    editForm.description = service.description ?? '';
    editForm.duration_minutes = service.duration_minutes;
    editForm.break_minutes = service.break_minutes;
    editForm.price_display = centsToMaskedBrl(service.price_cents);
    editForm.is_active = Boolean(service.is_active);
    editModalOpen.value = true;
};

const submitEdit = () => {
    editForm
        .transform((data) => ({
            ...data,
            provider_agenda_id: Number(data.provider_agenda_id),
            duration_minutes: Number(data.duration_minutes),
            break_minutes: Number(data.break_minutes),
            price_cents: maskedBrlToCents(data.price_display),
        }))
        .put(route('services.update', editingServiceId.value), {
            preserveScroll: true,
            onSuccess: () => {
                editModalOpen.value = false;
            },
        });
};

const toggleActive = (service) => {
    useForm({
        ...service,
        is_active: !service.is_active
    }).put(route('services.update', service.id), { preserveScroll: true });
};

const remove = (id) => {
    serviceToDelete.value = id;
    deleteConfirmOpen.value = true;
};

const confirmDeletion = () => {
    if (!serviceToDelete.value) return;
    
    useForm({}).delete(route('services.destroy', serviceToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            deleteConfirmOpen.value = false;
            serviceToDelete.value = null;
        }
    });
};
</script>

<template>
    <Head title="Serviços" />

    <AuthenticatedLayout>
        <div v-if="!selectedAgendaId" class="za-card p-6">
            <h2 class="flex items-center gap-2 font-[var(--za-font-display)] text-2xl font-bold">
                <Settings2 class="h-6 w-6" />
                Selecione ou crie uma agenda
            </h2>
            <p class="mt-2 text-sm text-[var(--za-text-muted)]">Os serviços ficam vinculados a uma agenda específica para que você possa organizar melhor seu atendimento.</p>
            <Link class="za-button za-button-primary mt-6 inline-flex" :href="route('provider.agendas.index')">Ir para Agendas</Link>
        </div>

        <div v-else class="space-y-6">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[#18181b]">
                        Serviços
                    </h1>
                    <p class="mt-1 text-sm text-gray-400">
                        {{ services.filter((s) => s.is_active).length }} serviços ativos
                    </p>
                </div>
                <button
                    @click="createModalOpen = true"
                    class="flex items-center gap-2 self-start rounded-xl bg-[#18181b] px-5 py-2.5 text-sm font-bold text-white transition-all hover:bg-black shadow-lg shadow-black/5"
                >
                    <Plus class="h-4 w-4" />
                    Novo serviço
                </button>
            </div>

            <!-- Search and category filter -->
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        type="text"
                        placeholder="Buscar serviço..."
                        v-model="search"
                        class="h-11 w-full rounded-xl border-gray-100 bg-white pl-10 pr-4 text-sm text-[#18181b] placeholder:text-gray-400 focus:border-[#18181b] focus:ring-1 focus:ring-[#18181b] transition-all outline-none"
                    />
                </div>
            </div>

            <!-- Category tabs -->
            <div v-if="uniqueCategories.length > 1" class="mb-6 flex flex-wrap gap-2 animate-in fade-in duration-500">
                <button
                    v-for="cat in uniqueCategories"
                    :key="cat"
                    @click="selectedCategory = cat"
                    :class="cn(
                        'rounded-full px-4 py-1.5 text-xs font-bold transition-all',
                        selectedCategory === cat
                            ? 'bg-[#18181b] text-white shadow-md'
                            : 'bg-white text-gray-400 border border-gray-100 hover:bg-gray-50 hover:text-[#18181b]'
                    )"
                >
                    {{ cat }}
                </button>
            </div>

            <!-- Service cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 pb-20">
                <div
                    v-for="service in filteredServices"
                    :key="service.id"
                    :class="cn(
                        'rounded-2xl border transition-all bg-white relative group min-h-[220px] flex flex-col',
                        !service.is_active ? 'opacity-60 grayscale-[0.5]' : 'hover:shadow-xl hover:shadow-black/5 hover:-translate-y-0.5 border-gray-100'
                    )"
                >
                    <div class="flex items-start justify-between px-6 pt-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gray-50 border border-gray-100 transition-colors group-hover:bg-[#18181b]/5">
                                <Scissors class="h-5 w-5 text-[#18181b]" />
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-sm font-bold text-[#18181b]">{{ service.name }}</h3>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <span v-if="!service.categories || service.categories.length === 0" class="text-[9px] font-bold uppercase tracking-widest text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">
                                        Geral
                                    </span>
                                    <span 
                                        v-for="cat in service.categories" 
                                        :key="cat"
                                        class="text-[9px] font-bold uppercase tracking-widest text-[#18181b] bg-[#18181b]/5 px-1.5 py-0.5 rounded"
                                    >
                                        {{ cat }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <button
                                @click="openMenu = openMenu === service.id ? null : service.id"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-all hover:bg-gray-50 hover:text-[#18181b]"
                                aria-label="Opções"
                            >
                                <MoreHorizontal class="h-4 w-4" />
                            </button>
                            <div v-if="openMenu === service.id" class="absolute right-0 top-9 z-20 w-44 rounded-xl border border-gray-100 bg-white p-1.5 shadow-2xl animate-in fade-in zoom-in-95 duration-200">
                                <button
                                    @click="openEditModal(service); openMenu = null"
                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-bold text-[#18181b] hover:bg-gray-50 transition-colors"
                                >
                                    <Pencil class="h-4 w-4" />
                                    Editar
                                </button>
                                <button
                                    @click="toggleActive(service); openMenu = null"
                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-bold text-[#18181b] hover:bg-gray-50 transition-colors"
                                >
                                    <component :is="service.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                    {{ service.is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                                <div class="my-1 border-t border-gray-50" />
                                <button
                                    @click="remove(service.id); openMenu = null"
                                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 transition-colors"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    Excluir
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-5 flex-1">
                        <p class="mb-4 text-xs font-medium leading-relaxed text-gray-400 line-clamp-2 min-h-[32px]">
                            {{ service.description || 'Sem descrição definida para este serviço.' }}
                        </p>
                        <div class="flex items-center gap-5">
                            <div class="flex items-center gap-2 bg-gray-50 py-1.5 px-3 rounded-lg border border-gray-100/50">
                                <Clock class="h-4 w-4 text-gray-400" />
                                <span class="text-xs font-bold text-[#18181b]">{{ service.duration_minutes }}min</span>
                            </div>
                            <div class="flex items-center gap-2 bg-[#10b981]/5 py-1.5 px-3 rounded-lg border border-[#10b981]/10">
                                <DollarSign class="h-4 w-4 text-[#10b981]" />
                                <span class="text-xs font-extrabold text-[#10b981]">
                                    {{ formatCentsToBrl(service.price_cents) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-50 px-6 py-4 flex items-center justify-between mt-auto">
                        <div class="flex items-center -space-x-2">
                             <div class="h-6 w-6 rounded-full border border-white bg-gray-100 flex items-center justify-center text-[8px] font-bold text-gray-500">
                                P
                             </div>
                             <span class="ml-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Profissional</span>
                        </div>
                        <div :class="cn('h-2 w-2 rounded-full', service.is_active ? 'bg-[#10b981]' : 'bg-gray-200')"></div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="filteredServices.length === 0" class="col-span-full flex flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-gray-100 bg-white py-16 px-6 text-center">
                    <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mb-4 transition-all hover:scale-110">
                        <Search class="h-8 w-8 text-gray-200" />
                    </div>
                    <p class="text-sm font-bold text-[#18181b]">Nenhum serviço encontrado</p>
                    <p class="text-xs text-gray-400 mt-1">Tente ajustar seus filtros ou busque por outro termo.</p>
                </div>
            </div>

            <!-- Modals -->
            <Modal :open="editModalOpen" @close="editModalOpen = false">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-xl text-[#18181b]">Editar serviço</h3>
                        <button @click="editModalOpen = false" class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-gray-50 text-gray-400 transition-all">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form class="grid gap-4" @submit.prevent="submitEdit">
                        <div class="space-y-1.5">
                            <InputLabel value="Nome do serviço" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                            <input v-model="editForm.name" class="w-full h-11 px-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium" type="text" />
                            <InputError :message="editForm.errors.name" />
                        </div>

                        <div class="space-y-1.5">
                            <InputLabel value="Categorias" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                            <div class="flex flex-wrap gap-2 mb-2">
                                <div v-for="(cat, idx) in editForm.categories" :key="idx" class="flex items-center gap-1.5 bg-[#18181b] text-white px-2.5 py-1 rounded-lg text-xs font-bold animate-in zoom-in-95">
                                    {{ cat }}
                                    <button @click.prevent="removeCategory('edit', idx)" class="hover:text-red-400">
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                            <div class="relative group">
                                <input 
                                    v-model="categoryInput" 
                                    @keydown.enter.prevent="addCategory('edit')"
                                    list="category-suggestions"
                                    class="w-full h-11 pl-10 pr-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-bold"
                                    placeholder="Adicione categorias e aperte Enter"
                                />
                                <Tag class="absolute left-3 top-3.5 h-4 w-4 text-gray-400 group-focus-within:text-black transition-colors" />
                            </div>
                            <p class="text-[10px] text-gray-400 font-medium">Ex: Casamento, Formatura, Promoção...</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <InputLabel value="Preço (R$)" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                                <input
                                    :value="editForm.price_display"
                                    class="w-full h-11 px-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium"
                                    type="text"
                                    inputmode="numeric"
                                    @input="editForm.price_display = applyMoneyMask($event.target.value)"
                                />
                                <InputError :message="editForm.errors.price_cents" />
                            </div>
                            <div class="space-y-1.5">
                                <InputLabel value="Duração (minutos)" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                                <input v-model="editForm.duration_minutes" class="w-full h-11 px-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium" type="number" min="5" />
                                <InputError :message="editForm.errors.duration_minutes" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <InputLabel value="Descrição" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                            <textarea v-model="editForm.description" class="w-full p-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium" rows="3"></textarea>
                            <InputError :message="editForm.errors.description" />
                        </div>

                        <div class="flex items-center gap-3 py-2">
                             <input v-model="editForm.is_active" type="checkbox" id="edit-active" class="h-5 w-5 rounded border-gray-200 text-black focus:ring-black" />
                             <label for="edit-active" class="text-sm font-bold text-[#18181b]">Serviço ativo para agendamento</label>
                        </div>

                        <div class="mt-4 flex gap-3">
                            <button type="submit" class="flex-1 h-11 bg-[#18181b] text-white rounded-xl text-sm font-bold hover:bg-black transition-all shadow-lg active:scale-95">Salvar alterações</button>
                            <button type="button" @click="editModalOpen = false" class="px-6 h-11 rounded-xl border border-gray-100 text-sm font-bold text-gray-400 hover:bg-gray-50 transition-all">Cancelar</button>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal :open="createModalOpen" @close="createModalOpen = false">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-xl text-[#18181b]">Novo serviço</h3>
                        <button @click="createModalOpen = false" class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-gray-50 text-gray-400 transition-all">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form class="grid gap-4" @submit.prevent="submitCreate">
                        <div class="space-y-1.5">
                            <InputLabel value="Nome do serviço" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                            <input v-model="createForm.name" class="w-full h-11 px-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium" type="text" placeholder="Ex.: Consulta inicial" />
                            <InputError :message="createForm.errors.name" />
                        </div>

                        <div class="space-y-1.5">
                            <InputLabel value="Categorias" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                            <div class="flex flex-wrap gap-2 mb-2" v-if="createForm.categories.length > 0">
                                <div v-for="(cat, idx) in createForm.categories" :key="idx" class="flex items-center gap-1.5 bg-[#18181b] text-white px-2.5 py-1 rounded-lg text-xs font-bold animate-in zoom-in-95">
                                    {{ cat }}
                                    <button @click.prevent="removeCategory('create', idx)" class="hover:text-red-400">
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                            </div>
                            <div class="relative group">
                                <input 
                                    v-model="categoryInput" 
                                    @keydown.enter.prevent="addCategory('create')"
                                    list="category-suggestions"
                                    class="w-full h-11 pl-10 pr-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-bold"
                                    placeholder="Digite e aperte Enter"
                                />
                                <Tag class="absolute left-3 top-3.5 h-4 w-4 text-gray-400 group-focus-within:text-black transition-colors" />
                            </div>
                            <p class="text-[10px] text-gray-400 font-medium">Você pode vincular este serviço a múltiplas categorias.</p>
                        </div>

                        <datalist id="category-suggestions">
                            <option v-for="cat in uniqueCategories.filter(c => c !== 'Todos')" :key="cat" :value="cat" />
                        </datalist>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <InputLabel value="Preço (R$)" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                                <input
                                    :value="createForm.price_display"
                                    class="w-full h-11 px-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="0,00"
                                    @input="createForm.price_display = applyMoneyMask($event.target.value)"
                                />
                                <InputError :message="createForm.errors.price_cents" />
                            </div>
                            <div class="space-y-1.5">
                                <InputLabel value="Duração (minutos)" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                                <input v-model="createForm.duration_minutes" class="w-full h-11 px-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium" type="number" min="5" />
                                <InputError :message="createForm.errors.duration_minutes" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <InputLabel value="Descrição" class="text-xs font-bold uppercase tracking-widest text-gray-400" />
                            <textarea v-model="createForm.description" class="w-full p-4 rounded-xl border border-gray-100 bg-gray-50 focus:ring-1 focus:ring-black outline-none transition-all text-sm font-medium" rows="3" placeholder="Descreva o serviço"></textarea>
                            <InputError :message="createForm.errors.description" />
                        </div>

                        <div class="flex items-center gap-3 py-2">
                             <input v-model="createForm.is_active" type="checkbox" id="create-active" class="h-5 w-5 rounded border-gray-200 text-black focus:ring-black" />
                             <label for="create-active" class="text-sm font-bold text-[#18181b]">Serviço ativo para agendamento</label>
                        </div>

                        <div class="mt-4 flex gap-3">
                            <button type="submit" class="flex-1 h-11 bg-[#18181b] text-white rounded-xl text-sm font-bold hover:bg-black transition-all shadow-lg active:scale-95">Salvar serviço</button>
                            <button type="button" @click="createModalOpen = false" class="px-6 h-11 rounded-xl border border-gray-100 text-sm font-bold text-gray-400 hover:bg-gray-50 transition-all">Cancelar</button>
                        </div>
                    </form>
                </div>
            </Modal>

            <FeedbackModal
                :open="deleteConfirmOpen"
                title="Excluir Serviço"
                message="Tem certeza que deseja excluir este serviço? Esta ação não pode ser desfeita."
                type="error"
                confirm-text="Sim, excluir"
                cancel-text="Cancelar"
                @close="deleteConfirmOpen = false"
                @confirm="confirmDeletion"
                @action="deleteConfirmOpen = false"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

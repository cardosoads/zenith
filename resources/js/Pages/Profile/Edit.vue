<script setup>
import { ref, computed } from 'vue';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import {
    Building2,
    Users,
    Bell,
    Link2,
    Palette,
    Shield,
    Camera,
    Mail,
    Phone,
    MapPin,
    Globe,
    Clock,
    Plus,
    ChevronRight,
    Check,
    Smartphone,
    MessageSquare,
    CalendarDays,
    Instagram,
    ExternalLink,
    Eye,
    EyeOff,
    Trash2,
    Save,
    Pencil,
    X,
    Loader2,
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    business: Object,
    team: Array,
    notifications: Object,
    integrations: Array,
    sessions: Array,
    appearance: Object,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const activeTab = ref('negocio');

const tabs = [
    { id: 'negocio', label: 'Negocio', icon: Building2 },
    { id: 'equipe', label: 'Equipe', icon: Users },
    { id: 'notificacoes', label: 'Notificacoes', icon: Bell },
    { id: 'integracao', label: 'Integracoes', icon: Link2 },
    { id: 'aparencia', label: 'Aparencia', icon: Palette },
    { id: 'seguranca', label: 'Seguranca', icon: Shield },
];

// ─── Tab 1: Negocio ───────────────────────────────────────────────

const businessForm = useForm({
    display_name: props.business?.display_name ?? '',
    cnpj: props.business?.cnpj ?? '',
    phone: props.business?.phone ?? '',
    address: props.business?.address ?? '',
    timezone: props.business?.timezone ?? 'America/Sao_Paulo',
    currency: props.business?.currency ?? 'BRL',
});

const submitBusiness = () => {
    businessForm.patch(route('profile.business.update'), {
        preserveScroll: true,
    });
};

const timezones = [
    { value: 'America/Sao_Paulo', label: 'Brasilia (GMT-3)' },
    { value: 'America/Belem', label: 'Belem (GMT-3)' },
    { value: 'America/Manaus', label: 'Manaus (GMT-4)' },
    { value: 'America/Rio_Branco', label: 'Rio Branco (GMT-5)' },
    { value: 'America/Noronha', label: 'Noronha (GMT-2)' },
];

const currencies = [
    { value: 'BRL', label: 'Real (R$)' },
    { value: 'USD', label: 'Dolar ($)' },
    { value: 'EUR', label: 'Euro (EUR)' },
];

const logoUploading = ref(false);
const logoFileInput = ref(null);

const businessInitial = computed(() => {
    const name = props.business?.display_name ?? '';
    return name.charAt(0).toUpperCase() || 'Z';
});

const triggerLogoUpload = () => {
    logoFileInput.value?.click();
};

const uploadLogo = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('logo', file);

    logoUploading.value = true;
    router.post(route('profile.logo.upload'), formData, {
        preserveScroll: true,
        onFinish: () => {
            logoUploading.value = false;
            if (logoFileInput.value) {
                logoFileInput.value.value = '';
            }
        },
    });
};

const deleteLogo = () => {
    router.delete(route('profile.logo.destroy'), {
        preserveScroll: true,
    });
};

// ─── Tab 2: Equipe ────────────────────────────────────────────────

const roleLabels = {
    admin: 'Administrador',
    profissional: 'Profissional',
    recepcionista: 'Recepcionista',
};

const showAddMember = ref(false);
const editingMemberId = ref(null);
const deletingMemberId = ref(null);

const teamForm = useForm({
    name: '',
    email: '',
    role: 'profissional',
});

const addMember = () => {
    teamForm.post(route('profile.team.store'), {
        preserveScroll: true,
        onSuccess: () => {
            teamForm.reset();
            showAddMember.value = false;
        },
    });
};

const cancelAddMember = () => {
    teamForm.reset();
    teamForm.clearErrors();
    showAddMember.value = false;
};

const updateMemberRole = (member, newRole) => {
    router.patch(route('profile.team.update', member.id), {
        role: newRole,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            editingMemberId.value = null;
        },
    });
};

const toggleMemberActive = (member) => {
    router.patch(route('profile.team.update', member.id), {
        is_active: !member.is_active,
    }, {
        preserveScroll: true,
    });
};

const confirmDeleteMember = (memberId) => {
    deletingMemberId.value = memberId;
};

const deleteMember = (memberId) => {
    router.delete(route('profile.team.destroy', memberId), {
        preserveScroll: true,
        onSuccess: () => {
            deletingMemberId.value = null;
        },
    });
};

const memberInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
};

// ─── Tab 3: Notificacoes ──────────────────────────────────────────

const notifForm = useForm({
    email_new_booking: props.notifications?.email_new_booking ?? false,
    email_cancellation: props.notifications?.email_cancellation ?? false,
    email_reminders: props.notifications?.email_reminders ?? false,
    whatsapp_confirmation: props.notifications?.whatsapp_confirmation ?? false,
    whatsapp_reminder_24h: props.notifications?.whatsapp_reminder_24h ?? false,
    whatsapp_cancellation: props.notifications?.whatsapp_cancellation ?? false,
});

const toggleNotification = (key) => {
    notifForm[key] = !notifForm[key];
    notifForm.patch(route('profile.notifications.update'), {
        preserveScroll: true,
    });
};

const emailNotifications = [
    { key: 'email_new_booking', label: 'Novos Agendamentos' },
    { key: 'email_cancellation', label: 'Cancelamentos' },
    { key: 'email_reminders', label: 'Lembretes automaticos' },
];

const whatsappNotifications = [
    { key: 'whatsapp_confirmation', label: 'Confirmacao de Agendamento' },
    { key: 'whatsapp_reminder_24h', label: 'Lembrete (24h antes)' },
    { key: 'whatsapp_cancellation', label: 'Aviso de Cancelamento' },
];

// ─── Tab 4: Integracoes ───────────────────────────────────────────

const integrationIcons = {
    whatsapp: MessageSquare,
    google_calendar: CalendarDays,
    instagram: Instagram,
};

const integrationStatusConfig = {
    connected: { label: 'Conectado', classes: 'bg-emerald-500/10 text-emerald-600' },
    disconnected: { label: 'Desconectado', classes: 'bg-red-500/10 text-red-600' },
    coming_soon: { label: 'Em breve', classes: 'bg-muted text-muted-foreground' },
};

// ─── Tab 5: Aparencia ─────────────────────────────────────────────

const linkCopied = ref(false);

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.appearance?.public_url ?? '');
        linkCopied.value = true;
        setTimeout(() => {
            linkCopied.value = false;
        }, 2000);
    } catch {
        // Fallback silencioso
    }
};

// ─── Tab 6: Seguranca / Sessions ──────────────────────────────────

const parseUserAgent = (ua) => {
    if (!ua) return { browser: 'Desconhecido', os: 'Desconhecido' };
    const browser = ua.match(/(Chrome|Firefox|Safari|Edge|Opera)/i)?.[1] ?? 'Navegador';
    const os = ua.match(/(Windows|Mac|Linux|Android|iPhone|iPad)/i)?.[1] ?? 'Dispositivo';
    return { browser, os: os === 'iPhone' || os === 'iPad' ? 'iOS' : os };
};

const deleteSession = (sessionId) => {
    router.delete(route('profile.sessions.destroy', sessionId), {
        preserveScroll: true,
    });
};

// ─── Shared input class ───────────────────────────────────────────

const inputClass = 'flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
const inputIconClass = 'flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
const selectClass = 'flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
const selectIconClass = 'flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';
</script>

<template>
    <Head title="Configuracoes" />

    <AuthenticatedLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">
                Configuracoes
            </h1>
            <p class="mt-1 text-muted-foreground">
                Gerencie as configuracoes do seu negocio e perfil.
            </p>
        </div>

        <!-- Tab navigation -->
        <div class="mb-8 overflow-x-auto">
            <div class="flex gap-1 border-b border-border">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="cn(
                        'flex shrink-0 items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold transition-all duration-200',
                        activeTab === tab.id
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                    )"
                >
                    <component :is="tab.icon" class="h-4 w-4" />
                    <span>{{ tab.label }}</span>
                </button>
            </div>
        </div>

        <!-- Tab content -->
        <div class="flex flex-col gap-8 max-w-5xl">

            <!-- ═══════════════════ NEGOCIO ═══════════════════ -->
            <div v-if="activeTab === 'negocio'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">

                <!-- Dados do Negocio -->
                <form @submit.prevent="submitBusiness">
                    <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                        <div class="border-b border-border px-6 py-4 bg-muted/30">
                            <h3 class="text-sm font-semibold text-foreground">Dados do Negocio</h3>
                            <p class="text-xs text-muted-foreground">Informacoes basicas que aparecem para seus clientes.</p>
                        </div>
                        <div class="p-6">
                            <!-- Logo -->
                            <div class="mb-8 flex items-center gap-6">
                                <div v-if="business?.logo_url" class="relative h-20 w-20 shrink-0">
                                    <img
                                        :src="business.logo_url"
                                        alt="Logo"
                                        class="h-20 w-20 rounded-2xl object-cover shadow-lg"
                                    />
                                    <button
                                        type="button"
                                        @click="deleteLogo"
                                        class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-destructive-foreground shadow-sm transition-all hover:bg-destructive/90"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </div>
                                <div v-else class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-primary shadow-lg shadow-primary/20 text-2xl font-bold text-primary-foreground">
                                    {{ businessInitial }}
                                </div>
                                <div class="space-y-2">
                                    <input
                                        ref="logoFileInput"
                                        type="file"
                                        accept="image/jpeg,image/png,image/svg+xml"
                                        class="hidden"
                                        @change="uploadLogo"
                                    />
                                    <button
                                        type="button"
                                        @click="triggerLogoUpload"
                                        :disabled="logoUploading"
                                        class="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-xs font-semibold text-foreground transition-all hover:bg-accent hover:shadow-sm disabled:opacity-50"
                                    >
                                        <Loader2 v-if="logoUploading" class="h-3.5 w-3.5 animate-spin" />
                                        <Camera v-else class="h-3.5 w-3.5" />
                                        {{ logoUploading ? 'Enviando...' : 'Alterar logo' }}
                                    </button>
                                    <p class="text-[10px] text-muted-foreground">JPG, PNG ou SVG. Max 2MB.</p>
                                </div>
                            </div>

                            <!-- Fields -->
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Nome do negocio</label>
                                    <input
                                        type="text"
                                        v-model="businessForm.display_name"
                                        :class="inputClass"
                                        placeholder="Nome do seu negocio"
                                    />
                                    <InputError :message="businessForm.errors.display_name" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">CNPJ</label>
                                    <input
                                        type="text"
                                        v-model="businessForm.cnpj"
                                        :class="inputClass"
                                        placeholder="00.000.000/0001-00"
                                    />
                                    <InputError :message="businessForm.errors.cnpj" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Email de Contato</label>
                                    <div class="relative">
                                        <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <input
                                            type="email"
                                            :value="business?.email ?? ''"
                                            readonly
                                            :class="cn(inputIconClass, 'bg-muted cursor-not-allowed')"
                                        />
                                    </div>
                                    <p class="text-[10px] text-muted-foreground">Altere o email na aba Seguranca.</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Telefone</label>
                                    <div class="relative">
                                        <Phone class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <input
                                            type="tel"
                                            v-model="businessForm.phone"
                                            :class="inputIconClass"
                                            placeholder="(00) 00000-0000"
                                        />
                                    </div>
                                    <InputError :message="businessForm.errors.phone" />
                                </div>
                                <div class="space-y-2 sm:col-span-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Endereco</label>
                                    <div class="relative">
                                        <MapPin class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <input
                                            type="text"
                                            v-model="businessForm.address"
                                            :class="inputIconClass"
                                            placeholder="Rua, numero - Cidade, UF"
                                        />
                                    </div>
                                    <InputError :message="businessForm.errors.address" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Regional -->
                    <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden mt-8">
                        <div class="border-b border-border px-6 py-4 bg-muted/30">
                            <h3 class="text-sm font-semibold text-foreground">Regional</h3>
                            <p class="text-xs text-muted-foreground">Configuracoes de fuso horario e moeda.</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Fuso Horario</label>
                                    <div class="relative">
                                        <Globe class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                        <select v-model="businessForm.timezone" :class="selectIconClass">
                                            <option v-for="tz in timezones" :key="tz.value" :value="tz.value">{{ tz.label }}</option>
                                        </select>
                                    </div>
                                    <InputError :message="businessForm.errors.timezone" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Moeda Padrao</label>
                                    <select v-model="businessForm.currency" :class="selectClass">
                                        <option v-for="c in currencies" :key="c.value" :value="c.value">{{ c.label }}</option>
                                    </select>
                                    <InputError :message="businessForm.errors.currency" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="mt-6 flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="businessForm.processing"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-primary-foreground shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50"
                        >
                            <Loader2 v-if="businessForm.processing" class="h-4 w-4 animate-spin" />
                            <Save v-else class="h-4 w-4" />
                            {{ businessForm.processing ? 'Salvando...' : 'Salvar dados do negocio' }}
                        </button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="businessForm.recentlySuccessful" class="text-sm text-emerald-600 font-medium">
                                Dados salvos com sucesso.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <!-- ═══════════════════ EQUIPE ═══════════════════ -->
            <div v-if="activeTab === 'equipe'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between border-b border-border px-6 py-4 bg-muted/30">
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Membros da Equipe</h3>
                            <p class="text-xs text-muted-foreground">{{ team?.length ?? 0 }} membros cadastrados.</p>
                        </div>
                        <button
                            @click="showAddMember = !showAddMember"
                            class="flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 active:scale-95 shadow-sm"
                        >
                            <Plus v-if="!showAddMember" class="h-3.5 w-3.5" />
                            <X v-else class="h-3.5 w-3.5" />
                            {{ showAddMember ? 'Cancelar' : 'Novo Membro' }}
                        </button>
                    </div>

                    <!-- Add member form -->
                    <div v-if="showAddMember" class="border-b border-border bg-accent/30 px-6 py-5">
                        <form @submit.prevent="addMember" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Nome</label>
                                    <input
                                        type="text"
                                        v-model="teamForm.name"
                                        :class="inputClass"
                                        placeholder="Nome completo"
                                    />
                                    <InputError :message="teamForm.errors.name" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Email</label>
                                    <input
                                        type="email"
                                        v-model="teamForm.email"
                                        :class="inputClass"
                                        placeholder="email@exemplo.com"
                                    />
                                    <InputError :message="teamForm.errors.email" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Funcao</label>
                                    <select v-model="teamForm.role" :class="selectClass">
                                        <option value="admin">Administrador</option>
                                        <option value="profissional">Profissional</option>
                                        <option value="recepcionista">Recepcionista</option>
                                    </select>
                                    <InputError :message="teamForm.errors.role" />
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button
                                    type="submit"
                                    :disabled="teamForm.processing"
                                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-primary-foreground shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50"
                                >
                                    <Loader2 v-if="teamForm.processing" class="h-3.5 w-3.5 animate-spin" />
                                    <Plus v-else class="h-3.5 w-3.5" />
                                    Adicionar membro
                                </button>
                                <button
                                    type="button"
                                    @click="cancelAddMember"
                                    class="rounded-lg border border-border px-4 py-2 text-xs font-bold text-muted-foreground transition-all hover:bg-accent"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Team list -->
                    <div class="divide-y divide-border">
                        <!-- Empty state -->
                        <div v-if="!team || team.length === 0" class="px-6 py-12 text-center">
                            <Users class="mx-auto h-10 w-10 text-muted-foreground/40" />
                            <p class="mt-3 text-sm font-medium text-muted-foreground">Nenhum membro na equipe</p>
                            <button
                                @click="showAddMember = true"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 active:scale-95 shadow-sm"
                            >
                                <Plus class="h-3.5 w-3.5" />
                                Adicionar primeiro membro
                            </button>
                        </div>

                        <div v-for="member in team" :key="member.id" class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-accent/50">
                            <div class="flex items-center gap-4">
                                <div v-if="member.avatar" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full overflow-hidden">
                                    <img :src="member.avatar" :alt="member.name" class="h-10 w-10 object-cover" />
                                </div>
                                <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                    {{ memberInitials(member.name) }}
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-foreground">{{ member.name }}</span>
                                        <span v-if="!member.is_active" class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-bold text-muted-foreground">Inativo</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ member.email }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Role select (inline edit) -->
                                <template v-if="editingMemberId === member.id">
                                    <select
                                        :value="member.role"
                                        @change="updateMemberRole(member, $event.target.value)"
                                        class="rounded-lg border border-input bg-background px-2 py-1 text-xs font-semibold ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    >
                                        <option value="admin">Administrador</option>
                                        <option value="profissional">Profissional</option>
                                        <option value="recepcionista">Recepcionista</option>
                                    </select>
                                    <button
                                        @click="editingMemberId = null"
                                        class="p-1 rounded-md hover:bg-muted text-muted-foreground transition-all"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </template>
                                <template v-else>
                                    <span :class="cn(
                                        'hidden rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider sm:inline',
                                        member.role === 'admin' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'
                                    )">
                                        {{ roleLabels[member.role] ?? member.role }}
                                    </span>
                                    <button
                                        @click="editingMemberId = member.id"
                                        class="p-1 rounded-md hover:bg-muted text-muted-foreground transition-all"
                                        title="Editar funcao"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                </template>

                                <!-- Toggle active -->
                                <button
                                    @click="toggleMemberActive(member)"
                                    :class="cn(
                                        'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
                                        member.is_active ? 'bg-primary' : 'bg-muted'
                                    )"
                                    :title="member.is_active ? 'Desativar membro' : 'Ativar membro'"
                                >
                                    <span :class="cn(
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-background shadow ring-0 transition duration-200',
                                        member.is_active ? 'translate-x-5' : 'translate-x-0'
                                    )" />
                                </button>

                                <!-- Delete -->
                                <template v-if="deletingMemberId === member.id">
                                    <div class="flex items-center gap-1">
                                        <button
                                            @click="deleteMember(member.id)"
                                            class="rounded-md bg-destructive px-2 py-1 text-[10px] font-bold text-destructive-foreground transition-all hover:bg-destructive/90"
                                        >
                                            Confirmar
                                        </button>
                                        <button
                                            @click="deletingMemberId = null"
                                            class="rounded-md border border-border px-2 py-1 text-[10px] font-bold text-muted-foreground transition-all hover:bg-accent"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </template>
                                <template v-else>
                                    <button
                                        @click="confirmDeleteMember(member.id)"
                                        class="p-1 rounded-md text-muted-foreground hover:text-destructive transition-colors"
                                        title="Remover membro"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════ NOTIFICACOES ═══════════════════ -->
            <div v-if="activeTab === 'notificacoes'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Canais de Notificacao</h3>
                        <p class="text-xs text-muted-foreground">Escolha como voce e seus clientes serao avisados.</p>
                    </div>
                    <div class="p-6 space-y-8">
                        <!-- Email Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Mail class="h-4 w-4" />
                                </div>
                                <h4 class="text-sm font-bold text-foreground">Notificacoes por E-mail</h4>
                            </div>
                            <div class="grid gap-4 pl-11">
                                <div v-for="item in emailNotifications" :key="item.key" class="flex items-center justify-between">
                                    <span class="text-sm text-foreground font-medium">{{ item.label }}</span>
                                    <button
                                        type="button"
                                        @click="toggleNotification(item.key)"
                                        :disabled="notifForm.processing"
                                        :class="cn(
                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50',
                                            notifForm[item.key] ? 'bg-primary' : 'bg-muted'
                                        )"
                                    >
                                        <span :class="cn(
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-background shadow ring-0 transition duration-200',
                                            notifForm[item.key] ? 'translate-x-5' : 'translate-x-0'
                                        )" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600">
                                    <MessageSquare class="h-4 w-4" />
                                </div>
                                <h4 class="text-sm font-bold text-foreground">WhatsApp Business</h4>
                            </div>
                            <div class="grid gap-4 pl-11">
                                <div v-for="item in whatsappNotifications" :key="item.key" class="flex items-center justify-between">
                                    <span class="text-sm text-foreground font-medium">{{ item.label }}</span>
                                    <button
                                        type="button"
                                        @click="toggleNotification(item.key)"
                                        :disabled="notifForm.processing"
                                        :class="cn(
                                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-50',
                                            notifForm[item.key] ? 'bg-primary' : 'bg-muted'
                                        )"
                                    >
                                        <span :class="cn(
                                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-background shadow ring-0 transition duration-200',
                                            notifForm[item.key] ? 'translate-x-5' : 'translate-x-0'
                                        )" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Saving indicator -->
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="notifForm.recentlySuccessful" class="text-sm text-emerald-600 font-medium">
                                Preferencias salvas.
                            </p>
                        </Transition>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════ INTEGRACAO ═══════════════════ -->
            <div v-if="activeTab === 'integracao'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30 text-foreground">
                        <h3 class="text-sm font-semibold">Ecossistema de Integracoes</h3>
                        <p class="text-xs text-muted-foreground">Conecte o Zenith com suas ferramentas favoritas.</p>
                    </div>
                    <div class="divide-y divide-border">
                        <!-- Empty state -->
                        <div v-if="!integrations || integrations.length === 0" class="px-6 py-12 text-center">
                            <Link2 class="mx-auto h-10 w-10 text-muted-foreground/40" />
                            <p class="mt-3 text-sm font-medium text-muted-foreground">Nenhuma integracao disponivel.</p>
                        </div>

                        <div v-for="integration in integrations" :key="integration.type" class="flex items-center justify-between px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted">
                                    <component
                                        :is="integrationIcons[integration.type] ?? Link2"
                                        class="h-6 w-6 text-muted-foreground"
                                    />
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-foreground">{{ integration.name }}</span>
                                        <span :class="cn(
                                            'text-[9px] font-bold uppercase tracking-widest px-1.5 py-0.5 rounded-full',
                                            integrationStatusConfig[integration.status]?.classes ?? 'bg-muted text-muted-foreground'
                                        )">
                                            {{ integrationStatusConfig[integration.status]?.label ?? integration.status }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ integration.description }}</span>
                                    <span v-if="integration.connected_at" class="text-[10px] text-muted-foreground/70 mt-0.5">
                                        Conectado em {{ integration.connected_at }}
                                    </span>
                                </div>
                            </div>
                            <button
                                :disabled="integration.status === 'coming_soon'"
                                :class="cn(
                                    'px-4 py-2 border border-border rounded-lg text-xs font-bold text-foreground transition-all ring-offset-background active:scale-95',
                                    integration.status === 'coming_soon'
                                        ? 'opacity-50 cursor-not-allowed'
                                        : 'hover:bg-accent'
                                )"
                            >
                                Gerenciar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════ APARENCIA ═══════════════════ -->
            <div v-if="activeTab === 'aparencia'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Identidade Visual</h3>
                        <p class="text-xs text-muted-foreground">Personalize como os clientes veem seu agendamento.</p>
                    </div>
                    <div class="p-6 space-y-8">
                        <!-- Theme info -->
                        <div class="space-y-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Tema da Pagina</label>
                            <div class="flex gap-4">
                                <div class="flex-1 flex flex-col items-center gap-3 rounded-xl border-2 border-primary p-4 transition-all group">
                                    <div class="aspect-video w-full rounded-lg bg-background border border-border shadow-inner" />
                                    <span class="text-xs font-bold text-foreground">Claro (Padrao)</span>
                                </div>
                                <div class="flex-1 flex flex-col items-center gap-3 rounded-xl border-2 border-border p-4 transition-all group">
                                    <div class="aspect-video w-full rounded-lg bg-slate-900 shadow-inner" />
                                    <span class="text-xs font-bold text-muted-foreground">Escuro Moderno</span>
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground bg-accent/50 rounded-lg px-3 py-2">
                                Temas sao configurados individualmente por agenda no Estudio de Incorporacao.
                            </p>
                        </div>

                        <!-- Public link -->
                        <div class="pt-8 border-t border-border">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">Link Publico</h4>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 relative">
                                    <ExternalLink class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <input
                                        type="text"
                                        readonly
                                        :value="appearance?.public_url ?? ''"
                                        :class="cn(inputIconClass, 'bg-muted')"
                                    />
                                </div>
                                <button
                                    @click="copyLink"
                                    :class="cn(
                                        'px-4 py-2 rounded-lg text-xs font-bold transition-all active:scale-95 shadow-sm',
                                        linkCopied
                                            ? 'bg-emerald-500 text-white shadow-emerald-500/20'
                                            : 'bg-primary text-primary-foreground hover:opacity-90 shadow-primary/20'
                                    )"
                                >
                                    <template v-if="linkCopied">
                                        <span class="flex items-center gap-1.5">
                                            <Check class="h-3.5 w-3.5" />
                                            Copiado!
                                        </span>
                                    </template>
                                    <template v-else>
                                        Copiar Link
                                    </template>
                                </button>
                            </div>
                        </div>

                        <!-- Agendas list -->
                        <div v-if="appearance?.agendas?.length" class="pt-8 border-t border-border">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">Suas Agendas</h4>
                            <div class="space-y-3">
                                <div
                                    v-for="agenda in appearance.agendas"
                                    :key="agenda.id"
                                    class="flex items-center justify-between rounded-xl border border-border px-4 py-3 transition-colors hover:bg-accent/50"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg" :style="{ backgroundColor: (agenda.accent ?? '#6366f1') + '1a' }">
                                            <CalendarDays class="h-4 w-4" :style="{ color: agenda.accent ?? '#6366f1' }" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-foreground">{{ agenda.name }}</span>
                                            <span class="text-[10px] text-muted-foreground">/w/{{ business?.slug }}/{{ agenda.slug }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span :class="cn(
                                            'text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full',
                                            agenda.is_published
                                                ? 'bg-emerald-500/10 text-emerald-600'
                                                : 'bg-muted text-muted-foreground'
                                        )">
                                            {{ agenda.is_published ? 'Publicada' : 'Rascunho' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════ SEGURANCA ═══════════════════ -->
            <div v-if="activeTab === 'seguranca'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <!-- Update Profile Info -->
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Perfil do Usuario</h3>
                        <p class="text-xs text-muted-foreground">Mantenha seus dados de acesso atualizados.</p>
                    </div>
                    <div class="p-6">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>
                </div>

                <!-- Update Password -->
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Seguranca da Conta</h3>
                        <p class="text-xs text-muted-foreground">Altere sua senha regularmente.</p>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Sessoes Ativas</h3>
                        <p class="text-xs text-muted-foreground">Dispositivos conectados a sua conta.</p>
                    </div>
                    <div class="divide-y divide-border">
                        <!-- Empty state -->
                        <div v-if="!sessions || sessions.length === 0" class="px-6 py-12 text-center">
                            <Globe class="mx-auto h-10 w-10 text-muted-foreground/40" />
                            <p class="mt-3 text-sm font-medium text-muted-foreground">Nenhuma sessao ativa encontrada.</p>
                        </div>

                        <div v-for="session in sessions" :key="session.id" class="flex items-center justify-between px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted">
                                    <Smartphone v-if="parseUserAgent(session.user_agent).os === 'Android' || parseUserAgent(session.user_agent).os === 'iOS'" class="h-5 w-5 text-muted-foreground" />
                                    <Globe v-else class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-foreground">
                                            {{ parseUserAgent(session.user_agent).browser }} no {{ parseUserAgent(session.user_agent).os }}
                                        </span>
                                        <span v-if="session.is_current" class="bg-emerald-500/10 text-emerald-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">Atual</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        {{ session.ip_address }}
                                        <template v-if="session.last_activity"> &middot; {{ session.last_activity }}</template>
                                    </span>
                                </div>
                            </div>
                            <button
                                v-if="!session.is_current"
                                @click="deleteSession(session.id)"
                                class="text-muted-foreground hover:text-destructive transition-colors"
                                title="Encerrar sessao"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="rounded-2xl border border-destructive/20 bg-destructive/5 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-destructive/10">
                        <h3 class="text-sm font-semibold text-destructive">Encerrar Conta</h3>
                    </div>
                    <div class="p-6">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Transicoes suaves para as abas */
.animate-in {
    animation-fill-mode: both;
}
</style>

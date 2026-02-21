<script setup>
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    Save
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const activeTab = ref('negocio');
const saved = ref(false);

const tabs = [
    { id: 'negocio', label: 'Negócio', icon: Building2 },
    { id: 'equipe', label: 'Equipe', icon: Users },
    { id: 'notificacoes', label: 'Notificações', icon: Bell },
    { id: 'integracao', label: 'Integrações', icon: Link2 },
    { id: 'aparencia', label: 'Aparência', icon: Palette },
    { id: 'seguranca', label: 'Segurança', icon: Shield },
];

const handleSave = () => {
    saved.value = true;
    setTimeout(() => {
        saved.value = false;
    }, 2000);
};

// Mock data for new sections
const teamMembers = [
    { id: '1', name: 'João Dias', email: 'joao@zenith.com', role: 'admin', active: true, avatar: 'JD' },
    { id: '2', name: 'Ana Costa', email: 'ana@zenith.com', role: 'profissional', active: true, avatar: 'AC' },
    { id: '3', name: 'Pedro Oliveira', email: 'pedro@zenith.com', role: 'profissional', active: true, avatar: 'PO' },
    { id: '4', name: 'Carla Santos', email: 'carla@zenith.com', role: 'profissional', active: true, avatar: 'CS' },
];

const roleLabels = {
    admin: 'Administrador',
    profissional: 'Profissional',
    recepcionista: 'Recepcionista',
};
</script>

<template>
    <Head title="Configurações" />

    <AuthenticatedLayout>
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Configurações
                </h1>
                <p class="mt-1 text-muted-foreground">
                    Gerencie as configurações do seu negócio e perfil.
                </p>
            </div>
            <button
                @click="handleSave"
                :class="cn(
                    'flex items-center gap-2 self-start rounded-xl px-4 py-2.5 text-sm font-semibold transition-all shadow-sm active:scale-95',
                    saved
                        ? 'bg-emerald-500 text-white'
                        : 'bg-primary text-primary-foreground hover:bg-primary/90'
                )"
            >
                <template v-if="saved">
                    <Check class="h-4 w-4" />
                    Salvo
                </template>
                <template v-else>
                    <Save class="h-4 w-4" />
                    Salvar alterações
                </template>
            </button>
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
            <!-- NEGOCIO -->
            <div v-if="activeTab === 'negocio'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Dados do Negócio</h3>
                        <p class="text-xs text-muted-foreground">Informações básicas que aparecem para seus clientes.</p>
                    </div>
                    <div class="p-6">
                        <div class="mb-8 flex items-center gap-6">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-primary shadow-lg shadow-primary/20 text-2xl font-bold text-primary-foreground">
                                Z
                            </div>
                            <div class="space-y-2">
                                <button class="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-xs font-semibold text-foreground transition-all hover:bg-accent hover:shadow-sm">
                                    <Camera class="h-3.5 w-3.5" />
                                    Alterar logo
                                </button>
                                <p class="text-[10px] text-muted-foreground">JPG, PNG ou SVG. Máx 2MB.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Nome do negócio</label>
                                <input type="text" value="Zenith Studio" class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">CNPJ</label>
                                <input type="text" value="12.345.678/0001-90" class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Email de Contato</label>
                                <div class="relative">
                                    <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <input type="email" value="contato@zenithstudio.com" class="flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Telefone</label>
                                <div class="relative">
                                    <Phone class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <input type="tel" value="(11) 3456-7890" class="flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                                </div>
                            </div>
                            <div class="space-y-2 sm:col-span-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Endereço</label>
                                <div class="relative">
                                    <MapPin class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <input type="text" value="Rua Augusta, 1234 - São Paulo, SP" class="flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regional -->
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Regional</h3>
                        <p class="text-xs text-muted-foreground">Configurações de fuso horário e moeda.</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Fuso Horário</label>
                                <div class="relative">
                                    <Globe class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <select class="flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                        <option>Brasília (GMT-3)</option>
                                        <option>Manaus (GMT-4)</option>
                                        <option>Nova Iorque (GMT-5)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Moeda Padrão</label>
                                <select class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                    <option>Real Brasileiro (R$)</option>
                                    <option>Dólar Americano ($)</option>
                                    <option>Euro (€)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EQUIPE -->
            <div v-if="activeTab === 'equipe'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between border-b border-border px-6 py-4 bg-muted/30">
                        <div>
                            <h3 class="text-sm font-semibold text-foreground">Membros da Equipe</h3>
                            <p class="text-xs text-muted-foreground">{{ teamMembers.length }} membros cadastrados.</p>
                        </div>
                        <button class="flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-xs font-bold text-primary-foreground transition-all hover:bg-primary/90 active:scale-95 shadow-sm">
                            <Plus class="h-3.5 w-3.5" />
                            Novo Membro
                        </button>
                    </div>
                    <div class="divide-y divide-border">
                        <div v-for="member in teamMembers" :key="member.id" class="flex items-center justify-between px-6 py-4 transition-colors hover:bg-accent/50">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                    {{ member.avatar }}
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-foreground">{{ member.name }}</span>
                                        <span v-if="!member.active" class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-bold text-muted-foreground">Inativo</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ member.email }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span :class="cn(
                                    'hidden rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider sm:inline',
                                    member.role === 'admin' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'
                                )">
                                    {{ roleLabels[member.role] }}
                                </span>
                                <button class="p-1 rounded-md hover:bg-muted text-muted-foreground transition-all">
                                    <ChevronRight class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NOTIFICACOES -->
            <div v-if="activeTab === 'notificacoes'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Canais de Notificação</h3>
                        <p class="text-xs text-muted-foreground">Escolha como você e seus clientes serão avisados.</p>
                    </div>
                    <div class="p-6 space-y-8">
                        <!-- Email Section -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Mail class="h-4 w-4" />
                                </div>
                                <h4 class="text-sm font-bold text-foreground">Notificações por E-mail</h4>
                            </div>
                            <div class="grid gap-4 pl-11">
                                <div v-for="item in ['Novos Agendamentos', 'Cancelamentos', 'Lembretes automáticos']" :key="item" class="flex items-center justify-between">
                                    <span class="text-sm text-foreground font-medium">{{ item }}</span>
                                    <button class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 bg-primary">
                                        <span class="translate-x-5 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-background shadow ring-0 transition duration-200"></span>
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
                                <div v-for="item in ['Confirmação de Agendamento', 'Lembrete (24h antes)', 'Aviso de Cancelamento']" :key="item" class="flex items-center justify-between">
                                    <span class="text-sm text-foreground font-medium">{{ item }}</span>
                                    <button class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 bg-primary">
                                        <span class="translate-x-5 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-background shadow ring-0 transition duration-200"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INTEGRACAO -->
            <div v-if="activeTab === 'integracao'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30 text-foreground">
                        <h3 class="text-sm font-semibold">Ecossistema de Integrações</h3>
                        <p class="text-xs text-muted-foreground">Conecte o Zenith com suas ferramentas favoritas.</p>
                    </div>
                    <div class="divide-y divide-border">
                        <div v-for="integration in [
                            { name: 'Google Calendar Sync', desc: 'Sincronize agendamentos automaticamente.', icon: CalendarDays, status: 'Conectado' },
                            { name: 'WhatsApp Business API', desc: 'Envie lembretes e mensagens automáticas.', icon: MessageSquare, status: 'Conectado' },
                            { name: 'Instagram Shopping', desc: 'Agendamentos direto pelo seu perfil.', icon: Instagram, status: 'Pendente' }
                        ]" :key="integration.name" class="flex items-center justify-between px-6 py-5">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted">
                                    <component :is="integration.icon" class="h-6 w-6 text-muted-foreground" />
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-foreground">{{ integration.name }}</span>
                                        <span :class="cn(
                                            'text-[9px] font-bold uppercase tracking-widest px-1.5 py-0.5 rounded-full',
                                            integration.status === 'Conectado' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'
                                        )">{{ integration.status }}</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ integration.desc }}</span>
                                </div>
                            </div>
                            <button class="px-4 py-2 border border-border rounded-lg text-xs font-bold text-foreground hover:bg-accent transition-all ring-offset-background active:scale-95">
                                Gerenciar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- APARENCIA -->
            <div v-if="activeTab === 'aparencia'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Identidade Visual</h3>
                        <p class="text-xs text-muted-foreground">Personalize como os clientes veem seu agendamento.</p>
                    </div>
                    <div class="p-6 space-y-8">
                        <div class="space-y-4">
                            <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Tema da Página</label>
                            <div class="flex gap-4">
                                <button class="flex-1 flex flex-col items-center gap-3 rounded-xl border-2 border-primary p-4 transition-all hover:bg-accent/50 group">
                                    <div class="aspect-video w-full rounded-lg bg-background border border-border shadow-inner" />
                                    <span class="text-xs font-bold text-foreground group-hover:text-primary">Claro (Padrão)</span>
                                </button>
                                <button class="flex-1 flex flex-col items-center gap-3 rounded-xl border-2 border-border p-4 transition-all hover:border-muted-foreground hover:bg-accent/50 group">
                                    <div class="aspect-video w-full rounded-lg bg-slate-900 shadow-inner" />
                                    <span class="text-xs font-bold text-muted-foreground group-hover:text-foreground">Escuro Moderno</span>
                                </button>
                            </div>
                        </div>

                        <div class="pt-8 border-t border-border">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-muted-foreground mb-4">Link Público</h4>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 relative">
                                    <ExternalLink class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <input type="text" readonly value="https://zenith.app/agendar/zenith-studio" class="flex h-10 w-full rounded-lg border border-input bg-muted pl-10 pr-3 py-2 text-sm text-muted-foreground" />
                                </div>
                                <button class="px-4 py-2 bg-primary text-primary-foreground rounded-lg text-xs font-bold hover:opacity-90 active:scale-95 shadow-sm shadow-primary/20">
                                    Copiar Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEGURANCA -->
            <div v-if="activeTab === 'seguranca'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
                <!-- Update Profile Info -->
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Perfil do Usuário</h3>
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
                        <h3 class="text-sm font-semibold text-foreground">Segurança da Conta</h3>
                        <p class="text-xs text-muted-foreground">Altere sua senha regularmente.</p>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm />
                    </div>
                </div>

                <!-- Active Sessions (Mock for layout) -->
                <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                    <div class="border-b border-border px-6 py-4 bg-muted/30">
                        <h3 class="text-sm font-semibold text-foreground">Sessões Ativas</h3>
                        <p class="text-xs text-muted-foreground">Dispositivos conectados à sua conta.</p>
                    </div>
                    <div class="divide-y divide-border">
                        <div v-for="session in [
                            { device: 'Chrome no Windows', location: 'São Paulo, SP', current: true, activity: 'Online' },
                            { device: 'Safari no iPhone', location: 'São Paulo, SP', current: false, activity: 'Há 2h' }
                        ]" :key="session.device" class="flex items-center justify-between px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted">
                                    <Globe class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-foreground">{{ session.device }}</span>
                                        <span v-if="session.current" class="bg-emerald-500/10 text-emerald-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">Atual</span>
                                    </div>
                                    <span class="text-xs text-muted-foreground">{{ session.location }} • {{ session.activity }}</span>
                                </div>
                            </div>
                            <button v-if="!session.current" class="text-muted-foreground hover:text-destructive transition-colors">
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
/* Transições suaves para as abas */
.animate-in {
    animation-fill-mode: both;
}
</style>

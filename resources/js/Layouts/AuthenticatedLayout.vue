<script setup>
import AgendaSelector from '@/Components/Provider/AgendaSelector.vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    CalendarCheck2,
    CalendarDays,
    ClipboardList,
    CreditCard,
    Grid2x2,
    LayoutDashboard,
    Mail,
    Monitor,
    Moon,
    Palette,
    Settings2,
    ShieldUser,
    Sun,
    Users,
    LogOut,
    UserCog,
    Menu,
    X,
    Bell,
    Search,
    ChevronDown
} from 'lucide-vue-next';

const page = usePage();
const mobileOpen = ref(false);
const sidebarCollapsed = ref(false);
const profileMenuOpen = ref(false);
const profileMenuRef = ref(null);
const currentTheme = ref('auto');

const user = computed(() => page.props.auth.user);
const roles = computed(() => page.props.auth.roles ?? []);
const providerAgendas = computed(() => page.props.providerContext?.agendas ?? []);
const selectedAgendaId = computed(() => page.props.providerContext?.selectedAgendaId ?? null);

const userInitials = computed(() => {
    const parts = (user.value?.name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2);

    return parts.length ? parts.map((part) => part[0]?.toUpperCase()).join('') : 'U';
});

const navItems = computed(() => {
    if (roles.value.includes('admin')) {
        return [
            { label: 'Dashboard', route: 'dashboard', icon: LayoutDashboard },
            { label: 'Prestadores', route: 'admin.providers.index', icon: ShieldUser },
            { label: 'Pagamentos', route: 'admin.payment-settings.index', icon: CreditCard },
        ];
    }

    return [
        { label: 'Dashboard', route: 'dashboard', icon: LayoutDashboard },
        { label: 'Minhas Agendas', route: 'provider.agendas.index', icon: Grid2x2 },
        { label: 'Serviços', route: 'services.index', icon: Settings2 },
        { label: 'Regras de Agenda', route: 'availability-rules.index', icon: CalendarDays },
        { label: 'Agendamentos', route: 'bookings.index', icon: CalendarCheck2 },
        { label: 'Clientes', route: 'customers.index', icon: Users },
        { label: 'Relatórios', route: 'reports.index', icon: BarChart3 },
        { label: 'Pagamentos PIX', route: 'provider.payment-settings.index', icon: CreditCard },
    ];
});

const providerRouteNames = [
    'dashboard',
    'services.index',
    'availability-rules.index',
    'bookings.index',
    'reports.index',
    'customers.index',
    'provider.agendas.index',
    'provider.payment-settings.index',
    'subscription.index'
];

const linkParams = (routeName) => {
    if (roles.value.includes('admin') || !selectedAgendaId.value) return {};
    if (!providerRouteNames.includes(routeName)) return {};
    return { agenda_id: selectedAgendaId.value };
};

const logout = () => {
    router.post(route('logout'));
};

onMounted(() => {
    const persisted = localStorage.getItem('za-sidebar-collapsed');
    sidebarCollapsed.value = persisted === '1';
    window.addEventListener('click', (e) => {
        if (profileMenuRef.value && !profileMenuRef.value.contains(e.target)) {
            profileMenuOpen.value = false;
        }
    });
});

watch(sidebarCollapsed, (value) => {
    localStorage.setItem('za-sidebar-collapsed', value ? '1' : '0');
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground font-sans antialiased">
        <!-- Dashboard Sidebar -->
        <aside 
            class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-border bg-card transition-all duration-300 ease-in-out"
            :class="[sidebarCollapsed ? 'w-20' : 'w-64', mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
        >
            <!-- Logo area -->
            <div class="flex h-16 items-center border-b border-border px-6">
                <Link href="/" class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold shadow-lg shadow-primary/20">
                        Z
                    </div>
                    <span v-if="!sidebarCollapsed" class="text-lg font-bold tracking-tight">Zenith</span>
                </Link>
            </div>

            <!-- Main navigation -->
            <nav class="flex-1 space-y-1 overflow-y-auto p-4 custom-scrollbar">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="route(item.route, linkParams(item.route))"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
                    :class="[
                        route().current(item.route + '*') 
                            ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/20' 
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                    ]"
                >
                    <component :is="item.icon" class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110" />
                    <span v-if="!sidebarCollapsed" class="truncate">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Bottom navigation -->
            <div class="border-t border-border p-4 space-y-1">
                <Link
                    v-if="!roles.includes('admin')"
                    :href="route('subscription.index', linkParams('subscription.index'))"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
                    :class="[
                        route().current('subscription.index') 
                            ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/20' 
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                    ]"
                >
                    <CreditCard class="h-4 w-4 shrink-0" />
                    <span v-if="!sidebarCollapsed">Assinatura</span>
                </Link>
                <Link
                    :href="route('profile.edit')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
                    :class="[
                        route().current('profile.edit') 
                            ? 'bg-primary text-primary-foreground shadow-sm shadow-primary/20' 
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground'
                    ]"
                >
                    <UserCog class="h-4 w-4 shrink-0" />
                    <span v-if="!sidebarCollapsed">Configurações</span>
                </Link>
                <button
                    @click="logout"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-all duration-200"
                >
                    <LogOut class="h-4 w-4 shrink-0" />
                    <span v-if="!sidebarCollapsed">Sair</span>
                </button>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div 
            v-if="mobileOpen" 
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
            @click="mobileOpen = false"
        ></div>

        <!-- Main content -->
        <div 
            class="flex flex-col transition-all duration-300 ease-in-out"
            :class="[sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64']"
        >
            <!-- Topbar header -->
            <header class="sticky top-0 z-30 flex h-16 w-full items-center border-b border-border bg-background/80 backdrop-blur-xl px-4 md:px-8">
                <button 
                    class="mr-4 rounded-md p-2 hover:bg-accent lg:hidden"
                    @click="mobileOpen = !mobileOpen"
                >
                    <Menu class="h-5 w-5" />
                </button>

                <!-- Search box -->
                <div class="hidden md:relative md:flex md:w-full md:max-w-xs">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input 
                        type="text" 
                        placeholder="Buscar..."
                        class="w-full rounded-full border border-border bg-muted/50 py-2 pl-9 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-all duration-200"
                    />
                </div>

                <div class="ml-auto flex items-center gap-2 md:gap-4">
                    <!-- Agenda Selector for Providers -->
                    <div v-if="!roles.includes('admin')" class="hidden sm:block min-w-[200px]">
                        <AgendaSelector inline compact :agendas="providerAgendas" :selected-agenda-id="selectedAgendaId" />
                    </div>

                    <button class="relative rounded-full p-2 text-muted-foreground hover:bg-accent hover:text-foreground transition-colors">
                        <Bell class="h-5 w-5" />
                        <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-primary ring-2 ring-background"></span>
                    </button>

                    <div class="relative" ref="profileMenuRef">
                        <button 
                            class="flex items-center gap-2 rounded-full border border-border bg-card p-1 pr-3 hover:bg-accent transition-colors"
                            @click="profileMenuOpen = !profileMenuOpen"
                        >
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground">
                                {{ userInitials }}
                            </div>
                            <span class="hidden text-sm font-medium md:inline-block truncate max-w-[100px]">{{ user.name }}</span>
                            <ChevronDown class="h-4 w-4 text-muted-foreground transition-transform" :class="{ 'rotate-180': profileMenuOpen }" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div 
                            v-if="profileMenuOpen" 
                            class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl border border-border bg-card p-2 shadow-xl ring-1 ring-black/5 animate-in fade-in zoom-in-95 duration-100"
                        >
                            <Link 
                                :href="route('profile.edit')" 
                                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-foreground hover:bg-accent transition-colors"
                                @click="profileMenuOpen = false"
                            >
                                <UserCog class="h-4 w-4 text-muted-foreground" />
                                Meu Perfil
                            </Link>
                            <div class="my-1 h-px bg-border"></div>
                            <button 
                                @click="logout"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors"
                            >
                                <LogOut class="h-4 w-4" />
                                Sair da conta
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <main class="flex-1 p-4 md:p-8">
                <div class="mx-auto max-w-7xl animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: transparent;
    border-radius: 10px;
}
.group:hover .custom-scrollbar::-webkit-scrollbar-thumb,
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: var(--border);
}
</style>


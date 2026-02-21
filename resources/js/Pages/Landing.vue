<script setup>
import { ref } from "vue"
import { Head, Link } from "@inertiajs/vue3"
import {
    Calendar,
    Clock,
    Users,
    CreditCard,
    BarChart3,
    Shield,
    ChevronRight,
    Check,
    Menu,
    X,
    Star,
    Zap,
    ArrowRight,
} from "lucide-vue-next"

const mobileMenuOpen = ref(false)
const openFaq = ref(null)

const NAV_LINKS = [
    { label: "Funcionalidades", href: "#funcionalidades" },
    { label: "Preços", href: "#precos" },
    { label: "Depoimentos", href: "#depoimentos" },
    { label: "FAQ", href: "#faq" },
]

const FEATURES = [
    {
        icon: Calendar,
        title: "Agendamento Online",
        description:
            "Seus clientes agendam serviços 24h pelo link personalizado, sem necessidade de ligações.",
    },
    {
        icon: Users,
        title: "Gestão de Clientes",
        description:
            "Cadastro completo com histórico de atendimentos, preferências e dados de contato.",
    },
    {
        icon: Clock,
        title: "Gestão de Agendas",
        description:
            "Crie múltiplas agendas com horários, profissionais e serviços independentes.",
    },
    {
        icon: CreditCard,
        title: "Pagamentos Integrados",
        description:
            "Receba pagamentos antecipados ou sinais diretamente na plataforma via Pix ou Stripe.",
    },
    {
        icon: BarChart3,
        title: "Relatórios Detalhados",
        description:
            "Acompanhe faturamento, atendimentos e desempenho com gráficos em tempo real.",
    },
    {
        icon: Shield,
        title: "Segurança Total",
        description:
            "Dados criptografados, backups automáticos e conformidade com a LGPD.",
    },
]

const PLANS = [
    {
        name: "Starter",
        price: "79",
        description: "Ideal para profissionais autônomos",
        features: [
            "1 profissional",
            "1 agenda",
            "Até 100 agendamentos/mês",
            "Link de agendamento",
            "Gestão de clientes",
            "Suporte por email",
        ],
        highlighted: false,
        cta: "Começar Agora",
        href: route('register'),
    },
    {
        name: "Profissional",
        price: "149",
        description: "Para equipes em crescimento",
        features: [
            "Até 5 profissionais",
            "Agendas ilimitadas",
            "Agendamentos ilimitados",
            "Pagamentos integrados",
            "Relatórios avançados",
            "Suporte prioritário",
            "Personalização de marca",
        ],
        highlighted: true,
        cta: "Assinar Agora",
        href: route('register'),
    },
    {
        name: "Empresa",
        price: "299",
        description: "Para negócios estabelecidos",
        features: [
            "Profissionais ilimitados",
            "Agendas ilimitadas",
            "Agendamentos ilimitados",
            "Pagamentos integrados",
            "Relatórios completos",
            "Suporte 24/7",
            "API de integração",
            "Gerente de conta dedicado",
        ],
        highlighted: false,
        cta: "Falar com Vendas",
        href: route('register'),
    },
]

const TESTIMONIALS = [
    {
        name: "Camila Ferreira",
        role: "Dona de Salão",
        quote:
            "Desde que comecei a usar o Zenith, reduzi em 70% as faltas dos clientes. O agendamento online mudou completamente meu negócio.",
        rating: 5,
    },
    {
        name: "Ricardo Almeida",
        role: "Barbeiro",
        quote:
            "A gestão de múltiplas agendas me permite atender em dois endereços sem confusão. Ferramenta essencial.",
        rating: 5,
    },
    {
        name: "Dra. Mariana Costa",
        role: "Clínica de Estética",
        quote:
            "Os relatórios me ajudam a entender quais serviços são mais procurados. Já aumentei meu faturamento em 40%.",
        rating: 5,
    },
]

const FAQS = [
    {
        question: "Posso testar antes de assinar?",
        answer:
            "Sim! Todos os planos contam com 14 dias de teste gratuito, sem necessidade de cartão de crédito.",
    },
    {
        question: "Posso mudar de plano a qualquer momento?",
        answer:
            "Claro. Você pode fazer upgrade ou downgrade do seu plano a qualquer momento, com ajuste proporcional na cobrança.",
    },
    {
        question: "Meus dados estão seguros?",
        answer:
            "Sim. Utilizamos criptografia de ponta a ponta e estamos em total conformidade com a LGPD.",
    },
    {
        question: "Como funciona o suporte?",
        answer:
            "Oferecemos suporte por email para o plano Starter, suporte prioritário para o Profissional e suporte 24/7 para o plano Empresa.",
    },
]

const STATS = [
    { value: "15.000+", label: "Negócios ativos" },
    { value: "2M+", label: "Agendamentos/mês" },
    { value: "99.9%", label: "Uptime garantido" },
    { value: "4.9/5", label: "Avaliação média" },
]

const toggleFaq = (index) => {
    openFaq.value = openFaq.value === index ? null : index
}
</script>

<template>
    <Head title="Zenith - Plataforma de Agendamento e Gestão" />

    <div class="min-h-screen bg-white text-foreground selection:bg-accent selection:text-accent-foreground">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-border/50 bg-background/80 backdrop-blur-lg">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link :href="route('home')" class="text-xl font-bold tracking-tight text-foreground">
                    Zenith
                </Link>

                <nav class="hidden items-center gap-8 md:flex">
                    <a
                        v-for="link in NAV_LINKS"
                        :key="link.href"
                        :href="link.href"
                        class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                    >
                        Entrar
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-lg bg-foreground px-4 py-2 text-sm font-medium text-background transition-opacity hover:opacity-90"
                    >
                        Começar Grátis
                    </Link>
                </div>

                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden"
                    aria-label="Abrir menu"
                >
                    <X v-if="mobileMenuOpen" class="h-5 w-5 text-foreground" />
                    <Menu v-else class="h-5 w-5 text-foreground" />
                </button>
            </div>

            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="border-t border-border bg-background px-6 pb-6 pt-4 md:hidden">
                <div class="flex flex-col gap-4">
                    <a
                        v-for="link in NAV_LINKS"
                        :key="link.href"
                        :href="link.href"
                        class="text-sm font-medium text-muted-foreground"
                        @click="mobileMenuOpen = false"
                    >
                        {{ link.label }}
                    </a>
                    <div class="mt-2 flex flex-col gap-3 border-t border-border pt-4">
                        <Link :href="route('login')" class="text-sm font-medium text-muted-foreground" @click="mobileMenuOpen = false">
                            Entrar
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-lg bg-foreground px-4 py-2.5 text-center text-sm font-medium text-background"
                            @click="mobileMenuOpen = false"
                        >
                            Começar Grátis
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="px-6 pb-20 pt-20 md:pt-32">
            <div class="mx-auto max-w-4xl text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-1.5 focus-within:ring-2 focus-within:ring-ring">
                    <Zap class="h-3.5 w-3.5 text-success" /> <!-- Changed emerald-500 to success from CSS vars -->
                    <span class="text-xs font-medium text-muted-foreground">
                        14 dias grátis - Sem cartão de crédito
                    </span>
                </div>

                <h1 class="text-balance text-4xl font-bold leading-tight tracking-tight text-foreground md:text-6xl md:leading-tight">
                    A plataforma completa para gerenciar seu negócio
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-pretty text-lg leading-relaxed text-muted-foreground md:text-xl">
                    Agendamento online, gestão de clientes, pagamentos integrados e
                    relatórios detalhados. Tudo em um só lugar.
                </p>

                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        :href="route('register')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-foreground px-6 py-3 text-sm font-semibold text-background transition-opacity hover:opacity-90 sm:w-auto"
                    >
                        Começar Grátis
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                    <a
                        href="#funcionalidades"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-border px-6 py-3 text-sm font-medium text-foreground transition-colors hover:bg-muted sm:w-auto"
                    >
                        Explorar Funcionalidades
                    </a>
                </div>
            </div>
        </section>

        <!-- Stats Bar -->
        <section class="border-y border-border bg-card">
            <div class="mx-auto grid max-w-6xl grid-cols-2 divide-x divide-border md:grid-cols-4">
                <div v-for="stat in STATS" :key="stat.label" class="px-6 py-8 text-center">
                    <p class="text-2xl font-bold text-foreground md:text-3xl">
                        {{ stat.value }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground uppercase tracking-wider">{{ stat.label }}</p>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="funcionalidades" class="px-6 py-20 md:py-28 scroll-mt-20">
            <div class="mx-auto max-w-6xl">
                <div class="mb-16 max-w-xl">
                    <span class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        Funcionalidades
                    </span>
                    <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                        Tudo que você precisa para crescer
                    </h2>
                    <p class="mt-4 text-pretty leading-relaxed text-muted-foreground">
                        Ferramentas poderosas e intuitivas para simplificar a gestão do seu
                        negócio de serviços.
                    </p>
                </div>

                <div class="grid gap-px overflow-hidden rounded-xl border border-border bg-border md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in FEATURES"
                        :key="feature.title"
                        class="flex flex-col gap-4 bg-background p-8 transition-colors hover:bg-muted/30"
                    >
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-card border border-border">
                            <component :is="feature.icon" class="h-5 w-5 text-foreground" />
                        </div>
                        <h3 class="text-base font-semibold text-foreground">
                            {{ feature.title }}
                        </h3>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing -->
        <section id="precos" class="border-t border-border bg-card px-6 py-20 md:py-28 scroll-mt-20">
            <div class="mx-auto max-w-6xl">
                <div class="mb-16 text-center">
                    <span class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        Preços
                    </span>
                    <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                        Escolha o plano ideal para você
                    </h2>
                    <p class="mt-4 text-pretty text-muted-foreground">
                        Todos os planos incluem 14 dias de teste gratuito. Cancele quando
                        quiser.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        v-for="plan in PLANS"
                        :key="plan.name"
                        :class="[
                            'relative flex flex-col rounded-xl border p-8 transition-all',
                            plan.highlighted
                                ? 'border-foreground bg-background shadow-xl scale-105 z-10'
                                : 'border-border bg-background hover:border-muted-foreground/50'
                        ]"
                    >
                        <div v-if="plan.highlighted" class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-foreground px-4 py-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-background">
                                Mais Popular
                            </span>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-foreground">
                                {{ plan.name }}
                            </h3>
                            <p class="mt-1 text-sm text-muted-foreground">
                                {{ plan.description }}
                            </p>
                        </div>

                        <div class="mb-8 flex items-baseline gap-1">
                            <span class="text-sm text-muted-foreground font-medium">R$</span>
                            <span class="text-4xl font-bold tracking-tight text-foreground">
                                {{ plan.price }}
                            </span>
                            <span class="text-sm text-muted-foreground">/mês</span>
                        </div>

                        <ul class="mb-8 flex flex-1 flex-col gap-3">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2.5">
                                <Check class="h-4 w-4 shrink-0 text-success" />
                                <span class="text-sm text-muted-foreground">
                                    {{ feature }}
                                </span>
                            </li>
                        </ul>

                        <Link
                            :href="plan.href"
                            :class="[
                                'flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-semibold transition-all',
                                plan.highlighted
                                    ? 'bg-foreground text-background hover:opacity-90'
                                    : 'border border-border bg-card text-foreground hover:bg-muted'
                            ]"
                        >
                            {{ plan.cta }}
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section id="depoimentos" class="px-6 py-20 md:py-28 scroll-mt-20">
            <div class="mx-auto max-w-6xl">
                <div class="mb-16 text-center">
                    <span class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        Depoimentos
                    </span>
                    <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                        Quem usa, recomenda
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div
                        v-for="t in TESTIMONIALS"
                        :key="t.name"
                        class="flex flex-col rounded-xl border border-border bg-card p-8 transition-transform hover:-translate-y-1"
                    >
                        <div class="mb-4 flex gap-1">
                            <Star
                                v-for="i in t.rating"
                                :key="i"
                                class="h-4 w-4 fill-amber-400 text-amber-400"
                            />
                        </div>
                        <p class="flex-1 text-sm leading-relaxed text-muted-foreground italic">
                            {{ `"${t.quote}"` }}
                        </p>
                        <div class="mt-6 border-t border-border pt-4">
                            <p class="text-sm font-semibold text-foreground">
                                {{ t.name }}
                            </p>
                            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">{{ t.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="border-t border-border bg-card px-6 py-20 md:py-28 scroll-mt-20">
            <div class="mx-auto max-w-2xl">
                <div class="mb-16 text-center">
                    <span class="text-xs font-semibold uppercase tracking-widest text-muted-foreground">
                        FAQ
                    </span>
                    <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                        Perguntas Frequentes
                    </h2>
                </div>

                <div class="flex flex-col gap-3">
                    <div v-for="(faq, i) in FAQS" :key="i" class="rounded-xl border border-border bg-background overflow-hidden">
                        <button
                            @click="toggleFaq(i)"
                            class="flex w-full items-center justify-between p-5 text-left transition-colors hover:bg-muted/20"
                        >
                            <span class="text-sm font-semibold text-foreground">
                                {{ faq.question }}
                            </span>
                            <ChevronRight
                                :class="[
                                    'h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200',
                                    openFaq === i ? 'rotate-90' : ''
                                ]"
                            />
                        </button>
                        <div 
                            v-show="openFaq === i" 
                            class="border-t border-border px-5 pb-5 pt-3 animate-in fade-in slide-in-from-top-2 duration-200"
                        >
                            <p class="text-sm leading-relaxed text-muted-foreground">
                                {{ faq.answer }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="px-6 py-20 md:py-28 border-t border-border">
            <div class="mx-auto max-w-4xl text-center">
                <h2 class="text-balance text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                    Pronto para transformar seu negócio?
                </h2>
                <p class="mt-4 text-pretty text-muted-foreground text-lg">
                    Junte-se a mais de 15.000 profissionais que já simplificaram sua
                    gestão com o Zenith.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        :href="route('register')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-foreground px-8 py-3.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 sm:w-auto shadow-lg shadow-foreground/10"
                    >
                        Começar Teste Grátis
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
                <p class="mt-4 text-xs text-muted-foreground">
                    14 dias grátis - Sem cartão de crédito - Cancele quando quiser
                </p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-border bg-card px-6 py-12">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 md:flex-row">
                <div class="flex items-center gap-6">
                    <span class="text-sm font-bold text-foreground">Zenith</span>
                    <span class="text-xs text-muted-foreground">
                        © 2026 Plataforma de agendamento e gestão
                    </span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-xs text-muted-foreground transition-colors hover:text-foreground">
                        Termos
                    </a>
                    <a href="#" class="text-xs text-muted-foreground transition-colors hover:text-foreground">
                        Privacidade
                    </a>
                    <a href="mailto:contato@zenith.com" class="text-xs text-muted-foreground transition-colors hover:text-foreground">
                        Contato
                    </a>
                </div>
            </div>
        </footer>
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
@keyframes slide-in-from-top-2 {
    from { transform: translateY(-0.5rem); }
    to { transform: translateY(0); }
}
.fade-in { animation-name: fade-in; }
.slide-in-from-top-2 { animation-name: slide-in-from-top-2; }
</style>

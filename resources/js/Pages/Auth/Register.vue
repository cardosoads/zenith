<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    Check,
    Lock,
    Shield,
    Zap,
    Eye,
    EyeOff,
    X,
    Calendar,
    Users,
    LinkIcon,
    BarChart3,
    Mail,
    ChevronDown,
} from 'lucide-vue-next'

// ── Password visibility ──────────────────────────────────────────────────────
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const passwordTouched = ref(false)
const confirmTouched = ref(false)

// ── Seguimento options ───────────────────────────────────────────────────────
const seguimentoOptions = [
    'Saúde e Bem-estar',
    'Beleza e Estética',
    'Educação e Cursos',
    'Consultoria',
    'Serviços Profissionais',
    'Fitness e Personal',
    'Pet e Veterinário',
    'Outro',
]

// ── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    name: '',
    email: '',
    cpf: '',
    phone: '',
    seguimento: '',
    business_name: '',
    password: '',
    password_confirmation: '',
})

// ── Input masks ──────────────────────────────────────────────────────────────
const formatCpf = (event) => {
    let value = event.target.value.replace(/\D/g, '')
    if (value.length > 11) value = value.slice(0, 11)

    if (value.length > 9) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4')
    } else if (value.length > 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3')
    } else if (value.length > 3) {
        value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2')
    }

    form.cpf = value
}

const formatPhone = (event) => {
    let value = event.target.value.replace(/\D/g, '')
    if (value.length > 11) value = value.slice(0, 11)

    if (value.length > 6) {
        value = value.replace(/(\d{2})(\d{5})(\d{1,4})/, '($1) $2-$3')
    } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d{1,5})/, '($1) $2')
    } else if (value.length > 0) {
        value = value.replace(/(\d{1,2})/, '($1')
    }

    form.phone = value
}

// ── Password validation ──────────────────────────────────────────────────────
const passwordChecks = computed(() => ({
    minLength: form.password.length >= 8,
    hasUppercase: /[A-Z]/.test(form.password),
    hasSpecial: /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?`~]/.test(form.password),
}))

const allChecksPassed = computed(
    () =>
        passwordChecks.value.minLength &&
        passwordChecks.value.hasUppercase &&
        passwordChecks.value.hasSpecial,
)

const passwordsMatch = computed(
    () => form.password === form.password_confirmation && form.password_confirmation.length > 0,
)

// ── Trial features ───────────────────────────────────────────────────────────
const trialFeatures = [
    { icon: Calendar, text: 'Agendamento online ilimitado' },
    { icon: Users, text: 'Gestão de clientes' },
    { icon: LinkIcon, text: 'Link de agendamento personalizado' },
    { icon: BarChart3, text: 'Relatórios básicos' },
    { icon: Mail, text: 'Suporte por email' },
]

// ── Submit ────────────────────────────────────────────────────────────────────
const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Criar conta grátis — Zenith" />

    <div class="min-h-screen bg-background text-foreground">
        <!-- ── Header ──────────────────────────────────────────────────── -->
        <header class="border-b border-border">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link
                    :href="route('home')"
                    class="text-lg font-bold tracking-tight text-foreground"
                >
                    Zenith
                </Link>

                <Link
                    :href="route('login')"
                    class="flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    Já tem conta?
                    <span class="font-medium text-foreground underline underline-offset-2">Entrar</span>
                </Link>
            </div>
        </header>

        <!-- ── Main ───────────────────────────────────────────────────── -->
        <main class="mx-auto max-w-6xl px-6 py-10 md:py-16">
            <!-- Hero -->
            <div class="mb-10 text-center">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-1.5">
                    <Zap class="h-3.5 w-3.5 text-emerald-500" />
                    <span class="text-xs font-medium text-muted-foreground">Sem cartão de crédito necessário</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                    Comece grátis por 7 dias
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Sem cartão de crédito. Cancele quando quiser.
                </p>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-8 lg:flex-row lg:gap-12">
                <!-- ── LEFT — Form ─────────────────────────────────────── -->
                <div class="flex-1 lg:max-w-xl">
                    <!-- Personal data -->
                    <section class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Dados pessoais
                        </h2>
                        <div class="flex flex-col gap-4">
                            <!-- Nome completo -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-muted-foreground">
                                    Nome completo
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Seu nome completo"
                                    class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                />
                                <p v-if="form.errors.name" class="text-[11px] text-red-400">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-muted-foreground">
                                    Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="seu@email.com"
                                    class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                />
                                <p v-if="form.errors.email" class="text-[11px] text-red-400">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- CPF + Telefone -->
                            <div class="flex gap-4">
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">
                                        CPF
                                    </label>
                                    <input
                                        :value="form.cpf"
                                        @input="formatCpf"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="000.000.000-00"
                                        class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    />
                                    <p v-if="form.errors.cpf" class="text-[11px] text-red-400">
                                        {{ form.errors.cpf }}
                                    </p>
                                </div>
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">
                                        Telefone
                                    </label>
                                    <input
                                        :value="form.phone"
                                        @input="formatPhone"
                                        type="tel"
                                        inputmode="numeric"
                                        placeholder="(00) 00000-0000"
                                        class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    />
                                    <p v-if="form.errors.phone" class="text-[11px] text-red-400">
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Business data -->
                    <section class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Sobre seu negócio
                        </h2>
                        <div class="flex flex-col gap-4">
                            <!-- Seguimento -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-muted-foreground">
                                    Seguimento do negócio
                                </label>
                                <div class="relative">
                                    <select
                                        v-model="form.seguimento"
                                        :class="[
                                            'w-full appearance-none rounded-lg border bg-background px-4 py-2.5 pr-10 text-sm outline-none transition-colors focus:border-foreground',
                                            form.seguimento
                                                ? 'border-border text-foreground'
                                                : 'border-border text-muted-foreground/50',
                                        ]"
                                    >
                                        <option value="" disabled>Selecione seu seguimento</option>
                                        <option
                                            v-for="option in seguimentoOptions"
                                            :key="option"
                                            :value="option"
                                            class="text-foreground"
                                        >
                                            {{ option }}
                                        </option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                </div>
                                <p v-if="form.errors.seguimento" class="text-[11px] text-red-400">
                                    {{ form.errors.seguimento }}
                                </p>
                            </div>

                            <!-- Nome do negócio -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-muted-foreground">
                                    Nome do negócio
                                </label>
                                <input
                                    v-model="form.business_name"
                                    type="text"
                                    placeholder="Ex: Studio Beleza Total"
                                    class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                />
                                <p v-if="form.errors.business_name" class="text-[11px] text-red-400">
                                    {{ form.errors.business_name }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Password -->
                    <section class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Crie sua senha
                        </h2>
                        <div class="flex flex-col gap-4">
                            <!-- Senha -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-muted-foreground">
                                    Senha
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Crie uma senha"
                                        @blur="passwordTouched = true"
                                        :class="[
                                            'w-full rounded-lg border bg-background px-4 py-2.5 pr-10 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50',
                                            passwordTouched && !allChecksPassed
                                                ? 'border-red-500/50 focus:border-red-500'
                                                : passwordTouched && allChecksPassed
                                                  ? 'border-emerald-500/50 focus:border-emerald-500'
                                                  : 'border-border focus:border-foreground',
                                        ]"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        <EyeOff v-if="showPassword" class="h-4 w-4" />
                                        <Eye v-else class="h-4 w-4" />
                                    </button>
                                </div>

                                <!-- Validation requirements -->
                                <div
                                    v-if="passwordTouched || form.password.length > 0"
                                    class="mt-1 flex flex-col gap-1"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <Check
                                            v-if="passwordChecks.minLength"
                                            class="h-3 w-3 text-emerald-500"
                                        />
                                        <X v-else class="h-3 w-3 text-red-400" />
                                        <span
                                            :class="[
                                                'text-[11px]',
                                                passwordChecks.minLength
                                                    ? 'text-emerald-500'
                                                    : 'text-red-400',
                                            ]"
                                        >
                                            Mínimo 8 caracteres
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Check
                                            v-if="passwordChecks.hasUppercase"
                                            class="h-3 w-3 text-emerald-500"
                                        />
                                        <X v-else class="h-3 w-3 text-red-400" />
                                        <span
                                            :class="[
                                                'text-[11px]',
                                                passwordChecks.hasUppercase
                                                    ? 'text-emerald-500'
                                                    : 'text-red-400',
                                            ]"
                                        >
                                            Uma letra maiúscula
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Check
                                            v-if="passwordChecks.hasSpecial"
                                            class="h-3 w-3 text-emerald-500"
                                        />
                                        <X v-else class="h-3 w-3 text-red-400" />
                                        <span
                                            :class="[
                                                'text-[11px]',
                                                passwordChecks.hasSpecial
                                                    ? 'text-emerald-500'
                                                    : 'text-red-400',
                                            ]"
                                        >
                                            Um caractere especial (!@#$%...)
                                        </span>
                                    </div>
                                </div>
                                <p v-if="form.errors.password" class="text-[11px] text-red-400">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Confirmar senha -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-medium text-muted-foreground">
                                    Confirmar senha
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        placeholder="Confirme sua senha"
                                        @blur="confirmTouched = true"
                                        :class="[
                                            'w-full rounded-lg border bg-background px-4 py-2.5 pr-10 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50',
                                            confirmTouched && !passwordsMatch
                                                ? 'border-red-500/50 focus:border-red-500'
                                                : confirmTouched && passwordsMatch
                                                  ? 'border-emerald-500/50 focus:border-emerald-500'
                                                  : 'border-border focus:border-foreground',
                                        ]"
                                    />
                                    <button
                                        type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        <EyeOff v-if="showConfirmPassword" class="h-4 w-4" />
                                        <Eye v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <div
                                    v-if="confirmTouched && form.password_confirmation.length > 0 && !passwordsMatch"
                                    class="mt-0.5 flex items-center gap-1.5"
                                >
                                    <X class="h-3 w-3 text-red-400" />
                                    <span class="text-[11px] text-red-400">
                                        As senhas não coincidem
                                    </span>
                                </div>
                                <div
                                    v-if="confirmTouched && passwordsMatch"
                                    class="mt-0.5 flex items-center gap-1.5"
                                >
                                    <Check class="h-3 w-3 text-emerald-500" />
                                    <span class="text-[11px] text-emerald-500">
                                        Senhas coincidem
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Submit button (mobile) -->
                    <div class="lg:hidden">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-lg bg-foreground py-3.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 disabled:opacity-60"
                        >
                            <span v-if="form.processing">Criando conta...</span>
                            <span v-else>Criar conta grátis</span>
                        </button>
                        <p class="mt-3 text-center text-[10px] text-muted-foreground">
                            7 dias grátis. Sem cartão de crédito necessário.
                        </p>
                        <p class="mt-4 text-center text-sm text-muted-foreground">
                            Já tem uma conta?
                            <Link
                                :href="route('login')"
                                class="font-medium text-foreground underline underline-offset-2"
                            >
                                Entrar
                            </Link>
                        </p>
                    </div>
                </div>

                <!-- ── RIGHT — Trial summary ──────────────────────────── -->
                <div class="lg:w-[380px]">
                    <div class="sticky top-24 rounded-xl border border-border bg-card p-6">
                        <h3 class="mb-6 text-base font-semibold text-foreground">
                            O que está incluso no teste grátis
                        </h3>

                        <!-- Trial badge -->
                        <div class="mb-5 rounded-lg bg-accent/50 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-foreground">
                                        Teste Grátis
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Acesso completo por 7 dias
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-emerald-500">
                                        R$ 0
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">/7 dias</p>
                                </div>
                            </div>
                        </div>

                        <!-- Features list -->
                        <div class="mb-6">
                            <p
                                class="mb-3 text-xs font-medium uppercase tracking-wider text-muted-foreground"
                            >
                                Tudo incluso
                            </p>
                            <ul class="flex flex-col gap-2.5">
                                <li
                                    v-for="feature in trialFeatures"
                                    :key="feature.text"
                                    class="flex items-center gap-2"
                                >
                                    <Check class="h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                    <span class="text-xs text-muted-foreground">{{ feature.text }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- After trial info -->
                        <div class="border-t border-border pt-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    Hoje você paga
                                </span>
                                <span class="text-xs font-medium text-emerald-500">
                                    R$ 0,00
                                </span>
                            </div>
                            <div class="border-t border-border pt-3">
                                <p class="text-[11px] text-muted-foreground">
                                    Após 7 dias, escolha um plano a partir de
                                    <span class="font-semibold text-foreground">R$ 49/mês</span>.
                                    Cancele a qualquer momento durante o teste.
                                </p>
                            </div>
                        </div>

                        <!-- Submit button (desktop) -->
                        <div class="mt-6 hidden lg:block">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-lg bg-foreground py-3.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 disabled:opacity-60"
                            >
                                <span v-if="form.processing">Criando conta...</span>
                                <span v-else>Criar conta grátis</span>
                            </button>
                            <p class="mt-3 text-center text-[10px] text-muted-foreground">
                                7 dias grátis. Sem cartão de crédito necessário.
                            </p>
                            <p class="mt-4 text-center text-sm text-muted-foreground">
                                Já tem uma conta?
                                <Link
                                    :href="route('login')"
                                    class="font-medium text-foreground underline underline-offset-2"
                                >
                                    Entrar
                                </Link>
                            </p>
                        </div>

                        <!-- Trust badges -->
                        <div
                            class="mt-6 flex items-center justify-center gap-4 border-t border-border pt-5"
                        >
                            <div class="flex items-center gap-1.5">
                                <Lock class="h-3 w-3 text-muted-foreground" />
                                <span class="text-[10px] text-muted-foreground">SSL Seguro</span>
                            </div>
                            <div class="h-3 w-px bg-border" />
                            <div class="flex items-center gap-1.5">
                                <Shield class="h-3 w-3 text-muted-foreground" />
                                <span class="text-[10px] text-muted-foreground">LGPD</span>
                            </div>
                            <div class="h-3 w-px bg-border" />
                            <div class="flex items-center gap-1.5">
                                <Zap class="h-3 w-3 text-muted-foreground" />
                                <span class="text-[10px] text-muted-foreground">7 dias grátis</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

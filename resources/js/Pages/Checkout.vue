<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import StripeCardElement from '@/Components/Payment/StripeCardElement.vue'
import {
    ArrowLeft,
    Check,
    Lock,
    Shield,
    Zap,
    Eye,
    EyeOff,
    X,
} from 'lucide-vue-next'

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    stripePublicKey: {
        type: String,
        default: null,
    },
})

// ── Plan data (fallback to static if no plans passed from backend) ──────────
const STATIC_PLANS = [
    {
        id: 'starter',
        name: 'Starter',
        price_cents: 7900,
        description: '1 profissional · 1 agenda',
        popular: false,
        features: [
            '1 profissional',
            '1 agenda',
            'Até 100 agendamentos/mês',
            'Link de agendamento',
            'Gestão de clientes',
            'Suporte por email',
        ],
    },
    {
        id: 'profissional',
        name: 'Profissional',
        price_cents: 14900,
        description: 'Até 5 profissionais · Ilimitado',
        popular: true,
        features: [
            'Até 5 profissionais',
            'Agendas ilimitadas',
            'Agendamentos ilimitados',
            'Pagamentos integrados',
            'Relatórios avançados',
            'Suporte prioritário',
            'Personalização de marca',
        ],
    },
    {
        id: 'empresa',
        name: 'Empresa',
        price_cents: 29900,
        description: 'Profissionais ilimitados',
        popular: false,
        features: [
            'Profissionais ilimitados',
            'Agendas ilimitadas',
            'Agendamentos ilimitados',
            'Pagamentos integrados',
            'Relatórios completos',
            'Suporte 24/7',
            'API de integração',
            'Gerente de conta dedicado',
        ],
    },
]

const availablePlans = computed(() =>
    props.plans && props.plans.length > 0 ? props.plans : STATIC_PLANS,
)

// ── Selected plan ────────────────────────────────────────────────────────────
const selectedPlanId = ref(
    availablePlans.value.find((p) => p.popular)?.id ?? availablePlans.value[0]?.id,
)

const selectedPlan = computed(
    () => availablePlans.value.find((p) => p.id === selectedPlanId.value) ?? availablePlans.value[0],
)

const planPrice = computed(() =>
    selectedPlan.value ? (selectedPlan.value.price_cents / 100).toFixed(0) : '0',
)

// ── Password visibility ──────────────────────────────────────────────────────
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const passwordTouched = ref(false)
const confirmTouched = ref(false)

// ── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
    plan_id: selectedPlanId.value,
    first_name: '',
    last_name: '',
    email: '',
    document: '',
    phone: '',
    business_name: '',
    password: '',
    password_confirmation: '',
})

// Keep plan_id in sync with selectedPlanId
const selectPlan = (id) => {
    selectedPlanId.value = id
    form.plan_id = id
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

// ── Stripe Payment ──────────────────────────────────────────────────────────
const stripeCardRef = ref(null)
const paymentProcessing = ref(false)
const paymentError = ref(null)

const submit = async () => {
    paymentError.value = null
    paymentProcessing.value = true

    try {
        const response = await fetch(route('checkout.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                Accept: 'application/json',
            },
            body: JSON.stringify(form.data()),
        })

        const data = await response.json()

        if (!response.ok) {
            if (data.errors) {
                form.clearErrors()
                Object.keys(data.errors).forEach((key) => {
                    form.setError(key, data.errors[key][0])
                })
            } else {
                paymentError.value = data.message || 'Erro ao processar. Tente novamente.'
            }
            paymentProcessing.value = false
            return
        }

        if (data.client_secret && stripeCardRef.value) {
            const result = await stripeCardRef.value.confirm(data.client_secret)

            if (!result.success) {
                paymentError.value = result.error?.message || 'Erro no pagamento.'
                paymentProcessing.value = false
                return
            }
        }

        router.post(route('checkout.confirm'), {}, {
            onFinish: () => {
                paymentProcessing.value = false
            },
        })
    } catch (e) {
        paymentError.value = 'Erro inesperado. Tente novamente.'
        paymentProcessing.value = false
    }
}
</script>

<template>
    <Head title="Checkout — Zenith" />

    <div class="min-h-screen bg-background text-foreground">
        <!-- ── Header ──────────────────────────────────────────────────── -->
        <header class="border-b border-border">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <Link
                    :href="route('home')"
                    class="flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    <span class="hidden sm:inline">Voltar</span>
                </Link>

                <Link :href="route('home')" class="text-lg font-bold tracking-tight text-foreground">
                    Zenith
                </Link>

                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <Lock class="h-3.5 w-3.5" />
                    <span class="text-xs">Checkout seguro</span>
                </div>
            </div>
        </header>

        <!-- ── Main ───────────────────────────────────────────────────── -->
        <main class="mx-auto max-w-6xl px-6 py-10 md:py-16">
            <!-- Step indicator -->
            <div class="mb-10 flex items-center justify-center gap-3">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-foreground text-xs font-semibold text-background"
                    >
                        1
                    </div>
                    <span class="text-sm font-medium text-foreground">Plano</span>
                </div>
                <div class="h-px w-8 bg-border" />
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full bg-foreground text-xs font-semibold text-background"
                    >
                        2
                    </div>
                    <span class="text-sm font-medium text-foreground">Dados</span>
                </div>
                <div class="h-px w-8 bg-border" />
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-6 w-6 items-center justify-center rounded-full border border-border text-xs font-medium text-muted-foreground"
                    >
                        3
                    </div>
                    <span class="text-sm text-muted-foreground">Confirmação</span>
                </div>
            </div>

            <form @submit.prevent="submit" class="flex flex-col gap-8 lg:flex-row lg:gap-12">
                <!-- ── LEFT — Form ─────────────────────────────────────── -->
                <div class="flex-1 lg:max-w-xl">
                    <!-- Plan selection -->
                    <section class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Escolha seu plano
                        </h2>
                        <div class="flex flex-col gap-3">
                            <button
                                v-for="plan in availablePlans"
                                :key="plan.id"
                                type="button"
                                @click="selectPlan(plan.id)"
                                :class="[
                                    'relative flex items-center justify-between rounded-xl border p-4 text-left transition-colors',
                                    selectedPlanId === plan.id
                                        ? 'border-foreground bg-card'
                                        : 'border-border hover:border-muted-foreground/40',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="[
                                            'flex h-5 w-5 items-center justify-center rounded-full border-2',
                                            selectedPlanId === plan.id
                                                ? 'border-foreground bg-foreground'
                                                : 'border-muted-foreground/30',
                                        ]"
                                    >
                                        <Check
                                            v-if="selectedPlanId === plan.id"
                                            class="h-3 w-3 text-background"
                                        />
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-foreground">
                                                {{ plan.name }}
                                            </span>
                                            <span
                                                v-if="plan.popular"
                                                class="rounded-full bg-foreground px-2 py-0.5 text-[10px] font-semibold text-background"
                                            >
                                                Popular
                                            </span>
                                        </div>
                                        <span class="text-xs text-muted-foreground">
                                            {{ plan.description }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-baseline gap-0.5">
                                    <span class="text-lg font-bold text-foreground">
                                        R$ {{ (plan.price_cents / 100).toFixed(0) }}
                                    </span>
                                    <span class="text-xs text-muted-foreground">/mês</span>
                                </div>
                            </button>
                        </div>
                    </section>

                    <!-- Personal data -->
                    <section class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Dados pessoais
                        </h2>
                        <div class="flex flex-col gap-4">
                            <!-- Name row -->
                            <div class="flex gap-4">
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">
                                        Nome
                                    </label>
                                    <input
                                        v-model="form.first_name"
                                        type="text"
                                        placeholder="Seu nome"
                                        class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    />
                                    <p v-if="form.errors.first_name" class="text-[11px] text-red-400">
                                        {{ form.errors.first_name }}
                                    </p>
                                </div>
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">
                                        Sobrenome
                                    </label>
                                    <input
                                        v-model="form.last_name"
                                        type="text"
                                        placeholder="Seu sobrenome"
                                        class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    />
                                </div>
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

                            <!-- Document + Phone -->
                            <div class="flex gap-4">
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">
                                        CPF / CNPJ
                                    </label>
                                    <input
                                        v-model="form.document"
                                        type="text"
                                        placeholder="000.000.000-00"
                                        class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    />
                                </div>
                                <div class="flex flex-1 flex-col gap-1.5">
                                    <label class="text-xs font-medium text-muted-foreground">
                                        Telefone
                                    </label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="(00) 00000-0000"
                                        class="rounded-lg border border-border bg-background px-4 py-2.5 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    />
                                </div>
                            </div>

                            <!-- Business name -->
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
                            </div>

                            <!-- Password -->
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

                            <!-- Confirm password -->
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

                    <!-- Payment -->
                    <section class="mb-8">
                        <h2 class="mb-4 text-lg font-semibold text-foreground">
                            Dados de pagamento
                        </h2>

                        <StripeCardElement
                            v-if="stripePublicKey"
                            ref="stripeCardRef"
                            :stripe-public-key="stripePublicKey"
                        />

                        <div v-else class="rounded-xl border border-border bg-card p-5">
                            <p class="text-sm text-muted-foreground text-center py-4">
                                Pagamento via cartão não está disponível no momento.
                            </p>
                        </div>

                        <p v-if="paymentError" class="mt-2 text-sm text-red-400">
                            {{ paymentError }}
                        </p>
                    </section>

                    <!-- Submit button (mobile) -->
                    <div class="lg:hidden">
                        <button
                            type="submit"
                            :disabled="paymentProcessing"
                            class="w-full rounded-lg bg-foreground py-3.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 disabled:opacity-60"
                        >
                            <span v-if="paymentProcessing">Processando...</span>
                            <span v-else>Assinar — R$ {{ planPrice }},00/mês</span>
                        </button>
                        <p class="mt-3 text-center text-[10px] text-muted-foreground">
                            14 dias grátis. Você não será cobrado agora. Cancele a qualquer
                            momento.
                        </p>
                    </div>
                </div>

                <!-- ── RIGHT — Order summary ──────────────────────────── -->
                <div class="lg:w-[380px]">
                    <div class="sticky top-24 rounded-xl border border-border bg-card p-6">
                        <h3 class="mb-6 text-base font-semibold text-foreground">
                            Resumo do pedido
                        </h3>

                        <!-- Selected plan highlight -->
                        <div class="mb-5 rounded-lg bg-accent/50 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-foreground">
                                        Plano {{ selectedPlan?.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Assinatura mensal
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-foreground">
                                        R$ {{ planPrice }}
                                    </p>
                                    <p class="text-[10px] text-muted-foreground">/mês</p>
                                </div>
                            </div>
                        </div>

                        <!-- Features list -->
                        <div class="mb-6">
                            <p
                                class="mb-3 text-xs font-medium uppercase tracking-wider text-muted-foreground"
                            >
                                Incluso no plano
                            </p>
                            <ul class="flex flex-col gap-2.5">
                                <li
                                    v-for="feature in selectedPlan?.features"
                                    :key="feature"
                                    class="flex items-center gap-2"
                                >
                                    <Check class="h-3.5 w-3.5 shrink-0 text-emerald-500" />
                                    <span class="text-xs text-muted-foreground">{{ feature }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Pricing breakdown -->
                        <div class="border-t border-border pt-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    Plano {{ selectedPlan?.name }}
                                </span>
                                <span class="text-xs text-foreground">
                                    R$ {{ planPrice }},00
                                </span>
                            </div>
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">
                                    Período de teste
                                </span>
                                <span class="text-xs font-medium text-emerald-500">
                                    14 dias grátis
                                </span>
                            </div>
                            <div class="border-t border-border pt-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-foreground">
                                        Total hoje
                                    </span>
                                    <span class="text-sm font-bold text-foreground">
                                        R$ 0,00
                                    </span>
                                </div>
                                <p class="mt-1 text-[10px] text-muted-foreground">
                                    Após o período de teste: R$ {{ planPrice }},00/mês
                                </p>
                            </div>
                        </div>

                        <!-- Submit button (desktop) -->
                        <div class="mt-6 hidden lg:block">
                            <button
                                type="submit"
                                :disabled="paymentProcessing"
                                class="w-full rounded-lg bg-foreground py-3.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 disabled:opacity-60"
                            >
                                <span v-if="paymentProcessing">Processando...</span>
                                <span v-else>Assinar — R$ {{ planPrice }},00/mês</span>
                            </button>
                            <p class="mt-3 text-center text-[10px] text-muted-foreground">
                                14 dias grátis. Você não será cobrado agora.
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
                                <span class="text-[10px] text-muted-foreground">Stripe</span>
                            </div>
                            <div class="h-3 w-px bg-border" />
                            <div class="flex items-center gap-1.5">
                                <Zap class="h-3 w-3 text-muted-foreground" />
                                <span class="text-[10px] text-muted-foreground">LGPD</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</template>

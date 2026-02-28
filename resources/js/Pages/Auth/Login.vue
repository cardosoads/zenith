<script setup>
import { ref, computed } from "vue"
import { Head, Link, useForm } from "@inertiajs/vue3"
import { Eye, EyeOff, ArrowRight, Calendar, Mail, Lock } from "lucide-vue-next"
import InputError from '@/Components/InputError.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false)

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const isFormValid = computed(() => {
    return form.email.length > 0 && form.password.length > 0
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Entrar na sua conta" />

    <div class="flex min-h-screen bg-white text-foreground selection:bg-accent selection:text-accent-foreground">
        <!-- Left side - branding panel -->
        <div class="hidden flex-col justify-between border-r border-border bg-card p-12 lg:flex lg:w-[480px] xl:w-[520px]">
            <div>
                <Link :href="route('home')" class="text-xl font-bold tracking-tight text-foreground">
                    Zenith
                </Link>
            </div>

            <div>
                <div class="mb-8 flex h-12 w-12 items-center justify-center rounded-xl border border-border bg-background">
                    <Calendar class="h-6 w-6 text-foreground" />
                </div>
                <h2 class="text-balance text-3xl font-bold leading-tight tracking-tight text-foreground">
                    Gerencie seu negócio de forma simples e eficiente.
                </h2>
                <p class="mt-4 text-pretty leading-relaxed text-muted-foreground">
                    Agendamento online, gestão de clientes e relatórios detalhados em uma única plataforma.
                </p>
            </div>

            <div class="flex flex-col gap-4">
                <div class="flex items-center gap-4 rounded-xl border border-border bg-background p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-card text-sm font-semibold text-foreground">
                        CF
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            "Reduzi 70% das faltas com o agendamento online do Zenith."
                        </p>
                        <p class="mt-1 text-xs font-medium text-foreground">
                            Camila Ferreira <span class="text-muted-foreground">- Dona de Salão</span>
                        </p>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    Mais de 15.000 negócios confiam no Zenith
                </p>
            </div>
        </div>

        <!-- Right side - login form -->
        <div class="flex flex-1 flex-col">
            <!-- Mobile header -->
            <div class="flex items-center justify-between border-b border-border p-6 lg:border-none lg:p-8">
                <Link :href="route('home')" class="text-lg font-bold tracking-tight text-foreground lg:hidden">
                    Zenith
                </Link>
                <div class="ml-auto flex items-center gap-1.5 text-sm text-muted-foreground">
                    <span class="hidden sm:inline">Não tem conta?</span>
                    <Link
                        :href="route('register')"
                        class="font-medium text-foreground underline-offset-4 hover:underline"
                    >
                        Criar conta
                    </Link>
                </div>
            </div>

            <!-- Form centered -->
            <div class="flex flex-1 items-center justify-center px-6 py-12">
                <div class="w-full max-w-sm">
                    <div class="mb-8">
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">
                            Entrar na sua conta
                        </h1>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Insira seus dados para acessar o painel.
                        </p>
                    </div>

                    <div v-if="status" class="mb-4 rounded-lg bg-success/10 p-3 text-sm font-medium text-success">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="flex flex-col gap-5">
                        <!-- Email -->
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-xs font-medium text-muted-foreground">
                                Email
                            </label>
                            <div class="relative">
                                <Mail class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/50" />
                                <input
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    placeholder="seu@email.com"
                                    autocomplete="email"
                                    required
                                    autofocus
                                    class="w-full rounded-lg border border-border bg-background py-2.5 pl-10 pr-4 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    :class="{ 'border-destructive': form.errors.email }"
                                />
                            </div>
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <label for="password" class="text-xs font-medium text-muted-foreground">
                                    Senha
                                </label>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-xs text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    Esqueceu a senha?
                                </Link>
                            </div>
                            <div class="relative">
                                <Lock class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/50" />
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    placeholder="Sua senha"
                                    autocomplete="current-password"
                                    required
                                    class="w-full rounded-lg border border-border bg-background py-2.5 pl-10 pr-10 text-sm text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                                    :class="{ 'border-destructive': form.errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground transition-colors hover:text-foreground"
                                    :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                                >
                                    <EyeOff v-if="showPassword" class="h-4 w-4" />
                                    <Eye v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Submit button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center justify-center gap-2 rounded-lg bg-foreground px-4 py-2.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <div v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-background border-t-transparent" />
                            <template v-else>
                                Entrar
                                <ArrowRight class="h-4 w-4" />
                            </template>
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="my-6 flex items-center gap-3">
                        <div class="h-px flex-1 bg-border" />
                        <span class="text-xs text-muted-foreground">ou</span>
                        <div class="h-px flex-1 bg-border" />
                    </div>

                    <!-- Social logins -->
                    <div class="flex flex-col gap-3">
                        <button
                            type="button"
                            class="flex items-center justify-center gap-3 rounded-lg border border-border bg-background px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-card"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24">
                                <path
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                                    fill="#4285F4"
                                />
                                <path
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                    fill="#34A853"
                                />
                                <path
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                                    fill="#FBBC05"
                                />
                                <path
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                    fill="#EA4335"
                                />
                            </svg>
                            Continuar com Google
                        </button>
                    </div>

                    <!-- Footer text for mobile -->
                    <p class="mt-8 text-center text-xs text-muted-foreground lg:hidden">
                        Mais de 15.000 negócios confiam no Zenith
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

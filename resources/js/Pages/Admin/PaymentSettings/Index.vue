<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import {
    Check,
    CreditCard,
    Eye,
    EyeOff,
    KeyRound,
    Wallet,
} from 'lucide-vue-next'

const props = defineProps({
    settings: Object,
})

const form = useForm({
    stripe_public_key: '',
    stripe_secret_key: '',
    stripe_webhook_secret: '',
    card_enabled: props.settings.card_enabled,
    pix_enabled: props.settings.pix_enabled,
})

const showPublicKey = ref(false)
const showSecretKey = ref(false)
const showWebhookSecret = ref(false)

const submit = () => {
    form.patch(route('admin.payment-settings.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Configurações de Pagamento" />

    <AuthenticatedLayout>
        <section class="space-y-6">
            <div>
                <p class="text-xs uppercase tracking-[0.15em] text-muted-foreground">Admin</p>
                <h2 class="text-3xl font-bold">Configurações de Pagamento</h2>
            </div>

            <form @submit.prevent="submit" class="space-y-8 max-w-2xl">
                <!-- Stripe connection status -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-lg',
                                    settings.has_stripe_keys
                                        ? 'bg-emerald-500/10 text-emerald-500'
                                        : 'bg-muted text-muted-foreground',
                                ]"
                            >
                                <CreditCard class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Stripe</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ settings.has_stripe_keys ? 'Conectado' : 'Não configurado' }}
                                </p>
                            </div>
                        </div>
                        <div
                            :class="[
                                'rounded-full px-2.5 py-1 text-[10px] font-semibold',
                                settings.has_stripe_keys
                                    ? 'bg-emerald-500/10 text-emerald-500'
                                    : 'bg-amber-500/10 text-amber-500',
                            ]"
                        >
                            {{ settings.has_stripe_keys ? 'Ativo' : 'Pendente' }}
                        </div>
                    </div>
                </div>

                <!-- API Keys -->
                <div class="rounded-xl border border-border bg-card p-5 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <KeyRound class="h-4 w-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">Chaves da API Stripe</h3>
                    </div>

                    <!-- Public Key -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-muted-foreground">
                            Chave Pública (Publishable Key)
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.stripe_public_key"
                                :type="showPublicKey ? 'text' : 'password'"
                                :placeholder="settings.stripe_public_key || 'pk_test_...'"
                                class="w-full rounded-lg border border-border bg-background px-4 py-2.5 pr-10 text-sm font-mono text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                            />
                            <button
                                type="button"
                                @click="showPublicKey = !showPublicKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <EyeOff v-if="showPublicKey" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.stripe_public_key" class="text-[11px] text-red-400">
                            {{ form.errors.stripe_public_key }}
                        </p>
                    </div>

                    <!-- Secret Key -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-muted-foreground">
                            Chave Secreta (Secret Key)
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.stripe_secret_key"
                                :type="showSecretKey ? 'text' : 'password'"
                                :placeholder="settings.stripe_secret_key || 'sk_test_...'"
                                class="w-full rounded-lg border border-border bg-background px-4 py-2.5 pr-10 text-sm font-mono text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                            />
                            <button
                                type="button"
                                @click="showSecretKey = !showSecretKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <EyeOff v-if="showSecretKey" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.stripe_secret_key" class="text-[11px] text-red-400">
                            {{ form.errors.stripe_secret_key }}
                        </p>
                    </div>

                    <!-- Webhook Secret -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-muted-foreground">
                            Webhook Secret
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.stripe_webhook_secret"
                                :type="showWebhookSecret ? 'text' : 'password'"
                                :placeholder="settings.stripe_webhook_secret || 'whsec_...'"
                                class="w-full rounded-lg border border-border bg-background px-4 py-2.5 pr-10 text-sm font-mono text-foreground outline-none transition-colors placeholder:text-muted-foreground/50 focus:border-foreground"
                            />
                            <button
                                type="button"
                                @click="showWebhookSecret = !showWebhookSecret"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <EyeOff v-if="showWebhookSecret" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <p v-if="form.errors.stripe_webhook_secret" class="text-[11px] text-red-400">
                            {{ form.errors.stripe_webhook_secret }}
                        </p>
                    </div>

                    <p class="text-[10px] text-muted-foreground">
                        Deixe em branco para manter as chaves atuais. As chaves são criptografadas no banco de dados.
                    </p>
                </div>

                <!-- Payment Methods -->
                <div class="rounded-xl border border-border bg-card p-5 space-y-4">
                    <div class="flex items-center gap-2 mb-2">
                        <Wallet class="h-4 w-4 text-muted-foreground" />
                        <h3 class="text-sm font-semibold text-foreground">Métodos de Pagamento</h3>
                    </div>

                    <!-- Card toggle -->
                    <div class="flex items-center justify-between rounded-lg border border-border px-4 py-3">
                        <div class="flex items-center gap-3">
                            <CreditCard class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="text-sm font-medium text-foreground">Cartão de Crédito/Débito</p>
                                <p class="text-xs text-muted-foreground">Via Stripe</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="form.card_enabled = !form.card_enabled"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200',
                                form.card_enabled ? 'bg-primary' : 'bg-muted',
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200',
                                    form.card_enabled ? 'translate-x-5' : 'translate-x-0',
                                ]"
                            />
                        </button>
                    </div>

                    <!-- PIX toggle -->
                    <div class="flex items-center justify-between rounded-lg border border-border px-4 py-3">
                        <div class="flex items-center gap-3">
                            <svg class="h-4 w-4 text-muted-foreground" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.78 6.22a3.5 3.5 0 0 0-4.95 0l-.83.83-.83-.83a3.5 3.5 0 0 0-4.95 4.95l.83.83-1.83 1.83a3.5 3.5 0 0 0 0 4.95 3.5 3.5 0 0 0 4.95 0l1.83-1.83.83.83a3.5 3.5 0 0 0 4.95-4.95l-.83-.83.83-.83a3.5 3.5 0 0 0 0-4.95z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-foreground">PIX</p>
                                <p class="text-xs text-muted-foreground">Pagamento instantâneo</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="form.pix_enabled = !form.pix_enabled"
                            :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200',
                                form.pix_enabled ? 'bg-primary' : 'bg-muted',
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200',
                                    form.pix_enabled ? 'translate-x-5' : 'translate-x-0',
                                ]"
                            />
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-foreground px-6 py-2.5 text-sm font-semibold text-background transition-opacity hover:opacity-90 disabled:opacity-60"
                    >
                        <span v-if="form.processing">Salvando...</span>
                        <span v-else>Salvar configurações</span>
                    </button>

                    <div
                        v-if="form.recentlySuccessful"
                        class="flex items-center gap-1.5 text-emerald-500"
                    >
                        <Check class="h-4 w-4" />
                        <span class="text-xs font-medium">Salvo com sucesso</span>
                    </div>
                </div>
            </form>
        </section>
    </AuthenticatedLayout>
</template>

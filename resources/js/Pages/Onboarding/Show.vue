<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StripeCardElement from '@/Components/Payment/StripeCardElement.vue'
import Button from '@/Components/UI/Button.vue'
import Card from '@/Components/UI/Card.vue'
import Badge from '@/Components/UI/Badge.vue'
import InputLabel from '@/Components/InputLabel.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    subscription: {
        type: Object,
        default: null,
    },
    stripePublicKey: {
        type: String,
        default: null,
    },
})

const form = useForm({
    plan_id: props.plans[0]?.id ?? null,
})

const stripeCardRef = ref(null)
const paymentProcessing = ref(false)
const paymentError = ref(null)
const showPayment = ref(false)

const checkout = async () => {
    paymentError.value = null
    paymentProcessing.value = true

    try {
        const { data } = await axios.post(route('onboarding.checkout'), { plan_id: form.plan_id })

        if (data.client_secret && stripeCardRef.value) {
            const result = await stripeCardRef.value.confirm(data.client_secret)

            if (!result.success) {
                paymentError.value = result.error?.message || 'Erro no pagamento.'
                paymentProcessing.value = false
                return
            }
        }

        router.post(route('onboarding.confirm'), {}, {
            onFinish: () => {
                paymentProcessing.value = false
            },
        })
    } catch (e) {
        paymentError.value = e.response?.data?.message || 'Erro inesperado. Tente novamente.'
        paymentProcessing.value = false
    }
}
</script>

<template>
    <Head title="Onboarding" />

    <AuthenticatedLayout>
        <section class="space-y-5 max-w-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.15em] text-muted-foreground">Etapa obrigatória</p>
                    <h2 class="text-3xl font-bold">Ative seu plano</h2>
                </div>
                <Badge :tone="subscription?.status === 'active' ? 'success' : 'warning'">
                    {{ subscription?.status ?? 'pending' }}
                </Badge>
            </div>

            <InputLabel value="Selecione um plano" />
            <div class="grid gap-4 md:grid-cols-2">
                <Card
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'cursor-pointer transition-all',
                        form.plan_id === plan.id ? 'ring-2 ring-primary' : '',
                    ]"
                    @click="form.plan_id = plan.id"
                >
                    <p class="mb-2 text-xs uppercase tracking-[0.12em] text-muted-foreground">{{ plan.billing_cycle }}</p>
                    <h3 class="text-2xl font-bold">{{ plan.name }}</h3>
                    <p class="mb-4 mt-1 text-sm text-muted-foreground">{{ plan.description }}</p>
                    <p class="font-mono text-xl font-semibold">R$ {{ (plan.price_cents / 100).toFixed(2) }}</p>
                    <label class="mt-4 flex items-center gap-2 text-sm">
                        <input v-model="form.plan_id" type="radio" :value="plan.id" /> Selecionar plano
                    </label>
                </Card>
            </div>

            <!-- Stripe payment section -->
            <div v-if="stripePublicKey" class="space-y-4">
                <h3 class="text-lg font-semibold text-foreground">Dados de pagamento</h3>
                <StripeCardElement
                    ref="stripeCardRef"
                    :stripe-public-key="stripePublicKey"
                />
            </div>

            <p v-if="paymentError" class="text-sm text-red-400">
                {{ paymentError }}
            </p>

            <div class="flex flex-wrap gap-2">
                <Button @click="checkout" :disabled="paymentProcessing">
                    {{ paymentProcessing ? 'Processando...' : 'Assinar plano' }}
                </Button>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import { useStripe } from '@/Composables/useStripe'
import { Shield } from 'lucide-vue-next'

const props = defineProps({
    stripePublicKey: {
        type: String,
        required: true,
    },
})

const emit = defineEmits(['ready', 'error'])

const cardError = ref(null)
const { loading, error: stripeError, initialize, mountCard, confirmPayment, destroy } = useStripe(props.stripePublicKey)

onMounted(async () => {
    await initialize()

    if (stripeError.value) {
        emit('error', stripeError.value)
        return
    }

    mountCard('#stripe-card-element')
    emit('ready')
})

onBeforeUnmount(() => {
    destroy()
})

const confirm = async (clientSecret) => {
    cardError.value = null

    const result = await confirmPayment(clientSecret)

    if (result.error) {
        cardError.value = result.error.message
        return { success: false, error: result.error }
    }

    return { success: true, paymentIntent: result.paymentIntent }
}

defineExpose({ confirm })
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-5">
        <div class="mb-4 flex items-center gap-2">
            <svg class="h-4 w-4 text-muted-foreground" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            <span class="text-xs font-medium text-muted-foreground">
                Cartão de crédito ou débito
            </span>
            <div class="ml-auto flex items-center gap-1">
                <div class="flex h-5 w-8 items-center justify-center rounded bg-[#1a1f71]">
                    <span class="text-[7px] font-bold italic text-white">VISA</span>
                </div>
                <div class="flex h-5 w-8 items-center justify-center rounded bg-[#eb001b]/90">
                    <span class="text-[7px] font-bold text-white">MC</span>
                </div>
            </div>
        </div>

        <div v-if="loading" class="animate-pulse rounded-lg bg-muted/50 py-5 px-4">
            <div class="h-4 w-3/4 rounded bg-muted"></div>
        </div>

        <div v-else>
            <div
                id="stripe-card-element"
                class="rounded-lg border border-border bg-background px-4 py-3 transition-colors focus-within:border-foreground"
            ></div>
        </div>

        <p v-if="cardError" class="mt-2 text-[11px] text-red-400">
            {{ cardError }}
        </p>

        <div class="mt-4 flex items-center gap-2 rounded-lg bg-accent/50 px-3 py-2">
            <Shield class="h-3.5 w-3.5 text-emerald-500" />
            <span class="text-[10px] text-muted-foreground">
                Pagamento processado de forma segura via Stripe. Seus dados
                nunca são armazenados em nossos servidores.
            </span>
        </div>
    </div>
</template>

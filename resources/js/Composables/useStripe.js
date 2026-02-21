import { loadStripe } from '@stripe/stripe-js'
import { ref } from 'vue'

let stripePromise = null

export function useStripe(publicKey) {
    const stripe = ref(null)
    const elements = ref(null)
    const cardElement = ref(null)
    const loading = ref(true)
    const error = ref(null)

    const initialize = async () => {
        if (!publicKey) {
            loading.value = false
            error.value = 'Chave pública do Stripe não configurada.'
            return
        }

        try {
            if (!stripePromise) {
                stripePromise = loadStripe(publicKey)
            }

            stripe.value = await stripePromise
            loading.value = false
        } catch (e) {
            error.value = 'Erro ao carregar o Stripe.'
            loading.value = false
        }
    }

    const mountCard = (elementId, options = {}) => {
        if (!stripe.value) return

        const isDark = document.documentElement.classList.contains('dark')

        elements.value = stripe.value.elements()
        cardElement.value = elements.value.create('card', {
            style: {
                base: {
                    color: isDark ? '#fafafa' : '#09090b',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    fontSize: '14px',
                    '::placeholder': {
                        color: isDark ? 'rgba(161,161,170,0.5)' : 'rgba(113,113,122,0.5)',
                    },
                },
                invalid: {
                    color: '#ef4444',
                },
            },
            hidePostalCode: true,
            ...options,
        })

        cardElement.value.mount(elementId)
    }

    const confirmPayment = async (clientSecret) => {
        if (!stripe.value || !cardElement.value) {
            return { error: { message: 'Stripe não inicializado.' } }
        }

        return await stripe.value.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement.value,
            },
        })
    }

    const destroy = () => {
        if (cardElement.value) {
            cardElement.value.destroy()
            cardElement.value = null
        }
        elements.value = null
    }

    return {
        stripe,
        cardElement,
        loading,
        error,
        initialize,
        mountCard,
        confirmPayment,
        destroy,
    }
}

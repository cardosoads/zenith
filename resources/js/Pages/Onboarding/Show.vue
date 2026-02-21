<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    subscription: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    plan_id: props.plans[0]?.id ?? null,
});

const checkout = () => {
    form.post(route('onboarding.checkout'));
};

const confirm = () => {
    form.post(route('onboarding.confirm'));
};
</script>

<template>
    <Head title="Onboarding" />

    <AuthenticatedLayout>
        <section class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.15em] text-[var(--za-text-muted)]">Etapa obrigatória</p>
                    <h2 class="font-[var(--za-font-display)] text-3xl">Ative seu plano</h2>
                </div>
                <Badge :tone="subscription?.status === 'active' ? 'success' : 'warning'">
                    {{ subscription?.status ?? 'pending' }}
                </Badge>
            </div>

            <InputLabel value="Selecione um plano" />
            <div class="grid gap-4 md:grid-cols-2">
                <Card v-for="plan in plans" :key="plan.id">
                    <p class="mb-2 text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">{{ plan.billing_cycle }}</p>
                    <h3 class="font-[var(--za-font-display)] text-2xl">{{ plan.name }}</h3>
                    <p class="mb-4 mt-1 text-sm text-[var(--za-text-muted)]">{{ plan.description }}</p>
                    <p class="font-mono text-xl font-semibold">R$ {{ (plan.price_cents / 100).toFixed(2) }}</p>
                    <label class="mt-4 flex items-center gap-2 text-sm">
                        <input v-model="form.plan_id" type="radio" :value="plan.id" /> Selecionar plano
                    </label>
                </Card>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button @click="checkout">Gerar checkout</Button>
                <Button variant="neutral" @click="confirm">Simular confirmação de pagamento</Button>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

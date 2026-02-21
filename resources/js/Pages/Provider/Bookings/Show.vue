<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
});

const formatMoney = (cents) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format((cents ?? 0) / 100);
};
</script>

<template>
    <Head :title="`Agendamento #${booking.id}`" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em] text-[var(--za-text-muted)]">Agendamento</p>
                    <h2 class="font-[var(--za-font-display)] text-3xl">#{{ booking.id }} · {{ booking.customer_name }}</h2>
                </div>
                <Link class="za-button za-button-neutral" :href="route('bookings.index', { agenda_id: booking.provider_agenda_id })">Voltar</Link>
            </div>

            <section class="grid gap-4 xl:grid-cols-2">
                <Card>
                    <h3 class="mb-3 font-[var(--za-font-display)] text-xl">Resumo</h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Agenda:</strong> {{ booking.provider_agenda?.name }}</p>
                        <p><strong>Serviço:</strong> {{ booking.service?.name }}</p>
                        <p><strong>Valor do serviço:</strong> {{ formatMoney(booking.service?.price_cents) }}</p>
                        <p><strong>Data/Hora:</strong> {{ new Date(booking.starts_at).toLocaleString('pt-BR') }}</p>
                        <p><strong>Status:</strong> <Badge>{{ booking.status }}</Badge></p>
                        <p><strong>Status pagamento:</strong> {{ booking.payment?.status ?? '—' }}</p>
                    </div>
                </Card>

                <Card>
                    <h3 class="mb-3 font-[var(--za-font-display)] text-xl">Dados do cliente</h3>
                    <div class="space-y-2 text-sm">
                        <p><strong>Nome:</strong> {{ booking.customer_name }}</p>
                        <p><strong>E-mail:</strong> {{ booking.customer_email }}</p>
                        <p><strong>Telefone:</strong> {{ booking.customer_phone ?? '—' }}</p>
                        <p><strong>Observações:</strong></p>
                        <p class="whitespace-pre-wrap border border-[var(--za-border)] bg-[var(--za-surface-2)] p-3 text-sm">
                            {{ booking.customer_notes || 'Sem observações.' }}
                        </p>
                    </div>
                </Card>
            </section>

            <Card>
                <h3 class="mb-3 font-[var(--za-font-display)] text-xl">Anexos</h3>
                <div v-if="booking.attachments?.length" class="grid gap-2 md:grid-cols-2">
                    <a
                        v-for="attachment in booking.attachments"
                        :key="attachment.id"
                        class="za-button za-button-neutral justify-between"
                        :href="route('booking-attachments.download', attachment.id)"
                    >
                        <span class="truncate">{{ attachment.original_name }}</span>
                        <span>{{ Math.ceil((attachment.size_bytes || 0) / 1024) }} KB</span>
                    </a>
                </div>
                <p v-else class="text-sm text-[var(--za-text-muted)]">Sem anexos enviados neste agendamento.</p>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

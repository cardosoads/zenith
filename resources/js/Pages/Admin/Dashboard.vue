<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    metrics: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <section class="space-y-6">
            <div>
                <p class="text-xs uppercase tracking-[0.15em] text-[var(--za-text-muted)]">Admin</p>
                <h2 class="font-[var(--za-font-display)] text-3xl">Visão operacional</h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="za-kpi">
                    <span>Prestadores</span>
                    <strong>{{ metrics.providers }}</strong>
                </article>
                <article class="za-kpi">
                    <span>Ativos</span>
                    <strong>{{ metrics.activeProviders }}</strong>
                </article>
                <article class="za-kpi">
                    <span>Usuários</span>
                    <strong>{{ metrics.users }}</strong>
                </article>
                <article class="za-kpi">
                    <span>Agendamentos</span>
                    <strong>{{ metrics.bookings }}</strong>
                </article>
            </div>

            <Link class="za-button za-button-primary" :href="route('admin.providers.index')">Gerenciar prestadores</Link>
        </section>
    </AuthenticatedLayout>
</template>

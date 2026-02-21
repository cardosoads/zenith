<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/UI/Table.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    providers: Array,
});

const activate = (id) => {
    useForm({}).post(route('admin.providers.activate', id));
};

const suspend = (id) => {
    useForm({}).post(route('admin.providers.suspend', id));
};
</script>

<template>
    <Head title="Prestadores" />

    <AuthenticatedLayout>
        <div class="space-y-4">
            <h2 class="font-[var(--za-font-display)] text-3xl">Prestadores</h2>

            <Table>
                <thead>
                    <tr>
                        <th>Prestador</th>
                        <th>Status</th>
                        <th>Plano</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="provider in providers" :key="provider.id">
                        <td>
                            <strong>{{ provider.display_name }}</strong>
                            <p class="text-xs text-[var(--za-text-muted)]">{{ provider.user?.email }}</p>
                        </td>
                        <td>{{ provider.status }}</td>
                        <td>{{ provider.current_subscription?.plan?.name ?? '—' }}</td>
                        <td class="flex gap-2">
                            <Button variant="neutral" @click="activate(provider.id)">Ativar</Button>
                            <Button variant="neutral" @click="suspend(provider.id)">Suspender</Button>
                        </td>
                    </tr>
                </tbody>
            </Table>
        </div>
    </AuthenticatedLayout>
</template>

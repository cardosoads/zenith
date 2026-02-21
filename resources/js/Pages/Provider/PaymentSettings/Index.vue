<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { 
    CreditCard, 
    QrCode, 
    Smartphone, 
    Mail, 
    FileText, 
    Check, 
    AlertCircle, 
    Save 
} from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    paymentSettings: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    pix_key: props.paymentSettings.pix_key ?? '',
    pix_key_type: props.paymentSettings.pix_key_type ?? 'cpf',
    pix_holder_name: props.paymentSettings.pix_holder_name ?? '',
    pix_holder_document: props.paymentSettings.pix_holder_document ?? '',
});

const submit = () => {
    form.patch(route('provider.payment-settings.update'), {
        preserveScroll: true,
    });
};

const keyTypes = [
    { value: 'cpf', label: 'CPF', icon: FileText },
    { value: 'cnpj', label: 'CNPJ', icon: FileText },
    { value: 'email', label: 'E-mail', icon: Mail },
    { value: 'phone', label: 'Telefone', icon: Smartphone },
    { value: 'random', label: 'Chave Aleatória', icon: QrCode },
];
</script>

<template>
    <Head title="Recebimento PIX" />

    <AuthenticatedLayout>
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-foreground">Recebimento PIX</h1>
                <p class="mt-1 text-muted-foreground">
                    Configure sua chave PIX para receber pagamentos dos seus serviços.
                </p>
            </div>
            
            <button
                @click="submit"
                :disabled="form.processing"
                :class="cn(
                    'flex items-center gap-2 self-start rounded-xl px-4 py-2.5 text-sm font-semibold transition-all shadow-sm active:scale-95 disabled:opacity-50',
                    form.recentlySuccessful
                        ? 'bg-emerald-500 text-white'
                        : 'bg-primary text-primary-foreground hover:bg-primary/90'
                )"
            >
                <template v-if="form.recentlySuccessful">
                    <Check class="h-4 w-4" />
                    Salvo com sucesso
                </template>
                <template v-else>
                    <Save class="h-4 w-4" />
                    Salvar configurações
                </template>
            </button>
        </div>

        <div class="max-w-3xl">
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden">
                <div class="border-b border-border px-8 py-6 bg-muted/30">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <QrCode class="h-6 w-6" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-foreground">Configuração da Chave PIX</h2>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Estas informações serão apresentadas aos seus clientes no momento do pagamento.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <form @submit.prevent="submit" class="space-y-8">
                        <!-- Alert Box -->
                        <div class="flex items-start gap-4 rounded-xl bg-[#3C3C3C] p-4 text-white">
                            <AlertCircle class="h-5 w-5 shrink-0 mt-0.5 text-amber-400" />
                            <div class="text-sm">
                                <p class="font-bold text-white">Atenção</p>
                                <p class="mt-1 text-gray-300">
                                    Serviços configurados como "pagamento antecipado" só ficarão disponíveis para agendamento após o preenchimento correto destes dados.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Tipo de Chave -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Tipo de Chave</label>
                                <div class="relative">
                                    <select
                                        v-model="form.pix_key_type"
                                        class="flex h-11 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option v-for="type in keyTypes" :key="type.value" :value="type.value">
                                            {{ type.label }}
                                        </option>
                                    </select>
                                </div>
                                <InputError :message="form.errors.pix_key_type" />
                            </div>

                            <!-- Chave PIX -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Chave PIX</label>
                                <input
                                    v-model="form.pix_key"
                                    type="text"
                                    placeholder="Informe sua chave"
                                    class="flex h-11 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <InputError :message="form.errors.pix_key" />
                            </div>

                            <!-- Nome do Titular -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Nome do Titular</label>
                                <input
                                    v-model="form.pix_holder_name"
                                    type="text"
                                    placeholder="Nome completo do titular"
                                    class="flex h-11 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <InputError :message="form.errors.pix_holder_name" />
                            </div>

                            <!-- Documento do Titular -->
                            <div class="space-y-2">
                                <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">CPF/CNPJ do Titular</label>
                                <input
                                    v-model="form.pix_holder_document"
                                    type="text"
                                    placeholder="Apenas números"
                                    class="flex h-11 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                />
                                <InputError :message="form.errors.pix_holder_document" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

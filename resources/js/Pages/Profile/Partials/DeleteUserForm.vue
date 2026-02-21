<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <p class="text-sm text-muted-foreground">
            Uma vez que sua conta for excluída, todos os seus recursos e dados serão permanentemente removidos. Por favor, tenha certeza antes de prosseguir.
        </p>

        <button 
            @click="confirmUserDeletion"
            class="inline-flex items-center justify-center rounded-lg bg-destructive px-4 py-2 text-sm font-bold text-destructive-foreground shadow-sm transition-all hover:bg-destructive/90 active:scale-95"
        >
            Excluir Minha Conta
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-8">
                <h2 class="text-xl font-bold text-foreground">
                    Você tem certeza que deseja excluir sua conta?
                </h2>

                <p class="mt-2 text-sm text-muted-foreground">
                    Esta ação é irreversível. Por favor, digite sua senha para confirmar que você gostaria de excluir permanentemente sua conta e todos os dados associados.
                </p>

                <div class="mt-6 space-y-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Senha de Confirmação</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Digite sua senha física para confirmar"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button 
                        @click="closeModal"
                        class="px-4 py-2 rounded-lg border border-border text-sm font-bold text-foreground hover:bg-accent transition-all"
                    >
                        Cancelar
                    </button>

                    <button
                        class="px-4 py-2 rounded-lg bg-destructive text-sm font-bold text-destructive-foreground hover:bg-destructive/90 transition-all shadow-sm active:scale-95 disabled:opacity-50"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Confirmar Exclusão
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>

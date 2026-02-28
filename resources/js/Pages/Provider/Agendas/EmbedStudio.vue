<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    provider: Object,
    agenda: Object,
    embedScriptUrl: String,
    widgetUrl: String,
});

const form = useForm({
    theme: props.agenda.theme,
    accent: props.agenda.accent,
    density: props.agenda.density,
    preset: props.agenda.preset,
    embed_height: props.agenda.embed_height,
    embed_width: props.agenda.embed_width || 480,
    transparent_bg: props.agenda.transparent_bg,
});

const previewUrl = computed(() => {
    const query = new URLSearchParams({
        theme: form.theme,
        accent: form.accent,
        density: form.density,
        preset: form.preset,
        transparent_bg: form.transparent_bg ? 1 : 0,
        width: form.embed_width,
    });

    return `${props.widgetUrl}?${query.toString()}`;
});

const embedCode = computed(() => {
    return `<div id="zenith-booking-widget"></div>\n<script src="${props.embedScriptUrl}" data-za-container="#zenith-booking-widget" data-za-provider="${props.provider.slug}" data-za-agenda="${props.agenda.slug}" data-za-theme="${form.theme}" data-za-accent="${form.accent}" data-za-density="${form.density}" data-za-preset="${form.preset}" data-za-height="${form.embed_height}" data-za-width="${form.embed_width}" data-za-transparent="${form.transparent_bg ? 1 : 0}"><\\/script>`;
});

const submit = () => {
    form.patch(route('provider.agendas.embed.update', props.agenda.id), {
        preserveScroll: true,
    });
};

const copyCode = async () => {
    await navigator.clipboard.writeText(embedCode.value);
};

const copyUrl = async () => {
    await navigator.clipboard.writeText(previewUrl.value);
};
</script>

<template>
    <Head title="Embed Studio" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-[0.14em] text-[var(--za-text-muted)]">Agenda</p>
                    <h2 class="font-[var(--za-font-display)] text-3xl">Embed Studio - {{ agenda.name }}</h2>
                </div>
                <div class="za-chip">{{ agenda.is_published ? 'Publicada' : 'Rascunho' }}</div>
            </div>

            <div class="grid gap-4 xl:grid-cols-12">
                <Card class="space-y-4 xl:col-span-4">
                    <h3 class="font-[var(--za-font-display)] text-xl">Aparência do widget</h3>

                    <div>
                        <InputLabel value="Tema" />
                        <select v-model="form.theme" class="za-select">
                            <option value="auto">Auto</option>
                            <option value="light">Claro</option>
                            <option value="dark">Escuro</option>
                        </select>
                        <InputError :message="form.errors.theme" />
                    </div>

                    <div>
                        <InputLabel value="Accent" />
                        <select v-model="form.accent" class="za-select">
                            <option value="sky">Sky</option>
                            <option value="honey">Honey</option>
                            <option value="green">Green</option>
                        </select>
                        <InputError :message="form.errors.accent" />
                    </div>

                    <div>
                        <InputLabel value="Densidade" />
                        <select v-model="form.density" class="za-select">
                            <option value="medium">Média</option>
                            <option value="comfortable">Confortável</option>
                        </select>
                        <InputError :message="form.errors.density" />
                    </div>

                    <div>
                        <InputLabel value="Preset Swiss" />
                        <select v-model="form.preset" class="za-select">
                            <option value="clean">Clean</option>
                            <option value="contrast">Contrast</option>
                            <option value="soft">Soft</option>
                            <option value="editorial">Editorial</option>
                        </select>
                        <InputError :message="form.errors.preset" />
                    </div>

                    <div>
                        <InputLabel value="Altura mínima do embed (px)" />
                        <input v-model="form.embed_height" class="za-input" type="number" min="400" max="2000" />
                        <InputError :message="form.errors.embed_height" />
                    </div>

                    <div>
                        <InputLabel value="Largura do embed (px)" />
                        <input v-model="form.embed_width" class="za-input" type="number" min="320" max="1200" />
                        <InputError :message="form.errors.embed_width" />
                    </div>

                    <div class="flex items-center gap-2 py-2">
                        <input v-model="form.transparent_bg" type="checkbox" id="transparent_bg" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                        <label for="transparent_bg" class="text-sm font-medium text-gray-700">Fundo transparente</label>
                    </div>

                    <div class="flex gap-2">
                        <Button type="button" @click="submit">Salvar estilos</Button>
                        <Button variant="neutral" type="button" @click="copyUrl">Copiar URL</Button>
                    </div>
                </Card>

                <Card class="space-y-4 xl:col-span-8">
                    <h3 class="font-[var(--za-font-display)] text-xl">Preview</h3>

                    <div class="grid gap-4 xl:grid-cols-2">
                        <div class="space-y-2">
                            <p class="text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">Desktop</p>
                            <iframe :src="previewUrl" class="h-[680px] w-full border border-[var(--za-border)]"></iframe>
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">Mobile</p>
                            <iframe :src="previewUrl" class="mx-auto h-[680px] w-[360px] max-w-full border border-[var(--za-border)]"></iframe>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Snippet embed" />
                        <textarea class="za-textarea" rows="4" readonly :value="embedCode"></textarea>
                    </div>

                    <div class="flex gap-2">
                        <Button type="button" @click="copyCode">Copiar snippet</Button>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

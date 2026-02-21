<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ChevronDown, LayoutGrid } from 'lucide-vue-next';

const props = defineProps({
    agendas: {
        type: Array,
        default: () => [],
    },
    selectedAgendaId: {
        type: [Number, null],
        default: null,
    },
    label: {
        type: String,
        default: 'Agenda',
    },
    compact: {
        type: Boolean,
        default: false,
    },
    inline: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const selectedValue = computed(() => {
    return props.selectedAgendaId ? String(props.selectedAgendaId) : '';
});

const changeAgenda = (event) => {
    const value = event.target.value;
    const url = page.url.split('?')[0];
    const query = new URLSearchParams(window.location.search);

    if (value) {
        query.set('agenda_id', value);
    } else {
        query.delete('agenda_id');
    }

    router.get(url, Object.fromEntries(query.entries()), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <div class="flex items-center gap-2">
        <label v-if="!compact" class="text-xs font-semibold uppercase tracking-wider text-muted-foreground whitespace-nowrap">
            {{ label }}:
        </label>
        
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-muted-foreground">
                <LayoutGrid class="h-4 w-4" />
            </div>
            
            <select 
                class="w-full appearance-none rounded-lg border border-border bg-muted/50 py-2 pl-9 pr-10 text-sm font-medium focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-all duration-200"
                :value="selectedValue" 
                @change="changeAgenda"
            >
                <option value="">Selecione uma agenda</option>
                <option v-for="agenda in agendas" :key="agenda.id" :value="String(agenda.id)">
                    {{ agenda.name }}
                </option>
            </select>
            
            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-muted-foreground">
                <ChevronDown class="h-4 w-4" />
            </div>
        </div>
    </div>
</template>


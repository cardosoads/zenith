<script setup>
import { ChevronLeft, ChevronRight, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    selectedDay: {
        type: String,
        required: true,
    },
    bookingsByDay: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:selectedDay', 'select-booking']);

const hours = Array.from({ length: 16 }, (_, index) => index + 7);

const parseDateKey = (dateKey) => {
    const [year, month, day] = String(dateKey)
        .split('-')
        .map((part) => Number(part));

    return new Date(year, (month || 1) - 1, day || 1, 12, 0, 0);
};

const dayBookings = computed(() => {
    return (props.bookingsByDay?.[props.selectedDay] ?? [])
        .slice()
        .sort((a, b) => {
            if (a.local_hour !== b.local_hour) {
                return a.local_hour - b.local_hour;
            }

            return String(a.local_time).localeCompare(String(b.local_time));
        });
});

const getBookingsForHour = (hour) => {
    return dayBookings.value.filter((booking) => {
        const hourFromField = Number(booking.local_hour);
        const hourFromTime = Number(String(booking.local_time ?? '').slice(0, 2));

        return hourFromField === hour || hourFromTime === hour;
    });
};

const moveDay = (direction) => {
    const base = parseDateKey(props.selectedDay);
    base.setDate(base.getDate() + direction);
    const year = base.getFullYear();
    const month = String(base.getMonth() + 1).padStart(2, '0');
    const day = String(base.getDate()).padStart(2, '0');

    emit('update:selectedDay', `${year}-${month}-${day}`);
};

const formatDayLabel = computed(() => {
    const date = parseDateKey(props.selectedDay);

    return date.toLocaleDateString('pt-BR', {
        weekday: 'long',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
});
</script>

<template>
    <section class="za-card p-4">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">Visualização diária</p>
                <h3 class="font-[var(--za-font-display)] text-lg capitalize">{{ formatDayLabel }}</h3>
            </div>

            <div class="flex items-center gap-2">
                <button class="za-button za-button-neutral" type="button" @click="moveDay(-1)">
                    <ChevronLeft class="h-4 w-4" />
                </button>
                <input
                    class="za-input min-w-[170px]"
                    type="date"
                    :value="selectedDay"
                    @input="emit('update:selectedDay', $event.target.value)"
                />
                <button class="za-button za-button-neutral" type="button" @click="moveDay(1)">
                    <ChevronRight class="h-4 w-4" />
                </button>
            </div>
        </div>

        <div class="space-y-2">
            <div
                v-for="hour in hours"
                :key="hour"
                class="grid grid-cols-[86px_minmax(0,1fr)] border border-[var(--za-border)]"
            >
                <div class="border-r border-[var(--za-border)] bg-[var(--za-surface-2)] px-3 py-2 text-xs text-[var(--za-text-muted)]">
                    {{ String(hour).padStart(2, '0') }}:00
                </div>
                <div class="min-h-[58px] px-2 py-1">
                    <template v-if="getBookingsForHour(hour).length">
                        <div
                            v-for="booking in getBookingsForHour(hour)"
                            :key="booking.id"
                            class="mb-1 cursor-pointer border border-[var(--za-border-strong)] bg-[var(--za-surface-2)] p-1 last:mb-0"
                            @click="emit('select-booking', booking)"
                        >
                            <p class="flex items-center justify-between gap-2 text-xs font-semibold">
                                <span>{{ booking.customer_name }}</span>
                                <Trash2 class="h-3 w-3" :class="booking.can_manage ? 'text-[var(--za-text)]' : 'text-[var(--za-text-muted)]'" />
                            </p>
                            <p class="text-[11px] text-[var(--za-text-muted)]">{{ booking.service?.name }}</p>
                            <p class="text-[11px] text-[var(--za-text-muted)]">{{ booking.local_time }}</p>
                        </div>
                    </template>
                    <p v-else class="pt-1 text-[11px] text-[var(--za-text-muted)]">Sem agendamento</p>
                </div>
            </div>
        </div>
    </section>
</template>

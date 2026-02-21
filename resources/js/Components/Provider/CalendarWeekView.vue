<script setup>
import { Trash2 } from 'lucide-vue-next';

const props = defineProps({
    weekDays: {
        type: Array,
        default: () => [],
    },
    bookingsByDay: {
        type: Object,
        default: () => ({}),
    },
});
const emit = defineEmits(['select-booking']);

const hours = Array.from({ length: 16 }, (_, index) => index + 7);

const getBookingsForHour = (dayKey, hour) => {
    return (props.bookingsByDay?.[dayKey] ?? [])
        .filter((booking) => Number(booking.local_hour) === hour)
        .sort((a, b) => new Date(a.starts_at) - new Date(b.starts_at));
};

const formatTime = (dateTime) => {
    return dateTime;
};
</script>

<template>
    <section class="za-card p-4">
        <div class="space-y-3 md:hidden">
            <article
                v-for="day in weekDays"
                :key="`mobile-${day.key}`"
                class="border border-[var(--za-border)] bg-[var(--za-surface-2)] p-3"
            >
                <p class="text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">{{ day.weekdayLabel }}</p>
                <p class="mb-2 font-[var(--za-font-display)]">{{ day.dayLabel }}</p>

                <div class="space-y-2">
                    <div
                        v-for="hour in hours"
                        :key="`${day.key}-${hour}`"
                        class="rounded-sm border border-[var(--za-border)] bg-[var(--za-surface)] p-2"
                    >
                        <p class="mb-1 text-[11px] uppercase tracking-[0.08em] text-[var(--za-text-muted)]">{{ String(hour).padStart(2, '0') }}:00</p>
                        <template v-if="getBookingsForHour(day.key, hour).length">
                            <div
                                v-for="booking in getBookingsForHour(day.key, hour)"
                                :key="booking.id"
                                class="mb-1 cursor-pointer border border-[var(--za-border-strong)] bg-[var(--za-surface-2)] p-1 last:mb-0"
                                @click="emit('select-booking', booking)"
                            >
                                <p class="flex items-center justify-between gap-2 text-xs font-semibold">
                                    <span>{{ booking.customer_name }}</span>
                                    <Trash2 class="h-3 w-3" :class="booking.can_manage ? 'text-[var(--za-text)]' : 'text-[var(--za-text-muted)]'" />
                                </p>
                                <p class="text-[11px] text-[var(--za-text-muted)]">{{ booking.service?.name }}</p>
                                <p class="text-[11px] text-[var(--za-text-muted)]">{{ formatTime(booking.local_time) }}</p>
                            </div>
                        </template>
                        <p v-else class="text-[11px] text-[var(--za-text-muted)]">Sem agendamento</p>
                    </div>
                </div>
            </article>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <div class="min-w-[980px]">
                <div class="grid grid-cols-[100px_repeat(7,minmax(0,1fr))] border-b border-[var(--za-border)] pb-2">
                    <p class="text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">Hora</p>
                    <div v-for="day in weekDays" :key="day.key" class="px-2">
                        <p class="text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]">{{ day.weekdayLabel }}</p>
                        <p class="font-[var(--za-font-display)]">{{ day.dayLabel }}</p>
                    </div>
                </div>

                <div
                    v-for="hour in hours"
                    :key="hour"
                    class="grid grid-cols-[100px_repeat(7,minmax(0,1fr))] border-b border-[var(--za-border)] py-1"
                >
                    <p class="text-xs text-[var(--za-text-muted)]">{{ String(hour).padStart(2, '0') }}:00</p>

                    <div
                        v-for="day in weekDays"
                        :key="`${day.key}-${hour}`"
                        class="min-h-[58px] border-l border-[var(--za-border)] px-2 py-1"
                    >
                        <template v-for="booking in getBookingsForHour(day.key, hour)" :key="booking.id">
                            <div
                                class="mb-1 border border-[var(--za-border-strong)] bg-[var(--za-surface-2)] p-1"
                                role="button"
                                tabindex="0"
                                @click="emit('select-booking', booking)"
                                @keydown.enter.prevent="emit('select-booking', booking)"
                            >
                                <p class="flex items-center justify-between gap-2 text-xs font-semibold">
                                    <span>{{ booking.customer_name }}</span>
                                    <Trash2 class="h-3 w-3" :class="booking.can_manage ? 'text-[var(--za-text)]' : 'text-[var(--za-text-muted)]'" />
                                </p>
                                <p class="text-[11px] text-[var(--za-text-muted)]">{{ booking.service?.name }}</p>
                                <p class="text-[11px] text-[var(--za-text-muted)]">{{ formatTime(booking.local_time) }}</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

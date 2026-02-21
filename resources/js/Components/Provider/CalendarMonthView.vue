<script setup>
const props = defineProps({
    monthDays: {
        type: Array,
        default: () => [],
    },
    weekdays: {
        type: Array,
        default: () => [],
    },
    bookingsByDay: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['select-day']);

const hasBookings = (date) => (props.bookingsByDay?.[date] ?? []).length > 0;
</script>

<template>
    <section class="za-card p-4">
        <div
            v-if="weekdays.length"
            class="mb-2 hidden gap-2 sm:grid"
            :style="{ gridTemplateColumns: `repeat(${weekdays.length}, minmax(0, 1fr))` }"
        >
            <p
                v-for="weekday in weekdays"
                :key="weekday.index"
                class="text-center text-xs uppercase tracking-[0.12em] text-[var(--za-text-muted)]"
            >
                {{ weekday.label }}
            </p>
        </div>

        <div
            class="grid grid-cols-2 gap-2 sm:grid-cols-4"
            :class="weekdays.length ? '' : 'md:grid-cols-7'"
            :style="weekdays.length ? { gridTemplateColumns: `repeat(${weekdays.length}, minmax(0, 1fr))` } : {}"
        >
            <button
                v-for="day in monthDays"
                :key="day.date"
                type="button"
                class="min-h-[92px] border p-2 text-left"
                :style="hasBookings(day.date)
                    ? 'border-color: var(--za-accent); background: color-mix(in srgb, var(--za-accent) 18%, var(--za-surface) 82%);'
                    : ''"
                @click="emit('select-day', day.date)"
            >
                <p class="text-sm font-semibold">{{ day.dayNumber }}</p>
                <p class="mt-2 text-xs text-[var(--za-text-muted)]">
                    {{ (bookingsByDay[day.date] ?? []).length }} ag.
                </p>
            </button>
        </div>
    </section>
</template>

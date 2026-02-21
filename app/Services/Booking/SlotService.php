<?php

namespace App\Services\Booking;

use App\Models\ProviderAgenda;
use App\Models\ProviderProfile;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SlotService
{
    /**
     * @return list<array{starts_at:string,ends_at:string}>
     */
    public function getAvailableSlots(
        ProviderProfile $providerProfile,
        ProviderAgenda $providerAgenda,
        int $serviceDuration,
        int $breakMinutes,
        string $date
    ): array {
        $timezone = $providerAgenda->timezone ?: $providerProfile->timezone;
        $targetDate = Carbon::parse($date, $timezone);

        $rules = $providerAgenda->availabilityRules()
            ->where('weekday', $targetDate->dayOfWeek)
            ->where('is_active', true)
            ->get();

        $slots = [];
        foreach ($rules as $rule) {
            $windowStart = Carbon::parse($targetDate->toDateString().' '.$rule->starts_at, $timezone);
            $windowEnd = Carbon::parse($targetDate->toDateString().' '.$rule->ends_at, $timezone);

            $step = max(5, $serviceDuration + $breakMinutes);
            foreach (CarbonPeriod::create($windowStart, $step.' minutes', $windowEnd->copy()->subMinutes($serviceDuration)) as $start) {
                $end = $start->copy()->addMinutes($serviceDuration);
                $busy = $providerAgenda->bookings()
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('starts_at', '<', $end->clone()->utc())
                    ->where('ends_at', '>', $start->clone()->utc())
                    ->exists();

                if (! $busy) {
                    $slots[] = [
                        'starts_at' => $start->toIso8601String(),
                        'ends_at' => $end->toIso8601String(),
                    ];
                }
            }
        }

        return $slots;
    }
}

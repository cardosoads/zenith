<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilityRuleRequest;
use App\Http\Requests\UpdateAvailabilityRuleRequest;
use App\Models\AvailabilityRule;
use App\Support\AgendaContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityRuleController extends Controller
{
    public function index(Request $request, AgendaContext $agendaContext): Response
    {
        $profile = $request->user()->providerProfile;
        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        $range = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        $start = $range['start'] ?? now($profile->timezone)->subDays(14)->toDateString();
        $end = $range['end'] ?? now($profile->timezone)->addDays(30)->toDateString();

        $startDate = Carbon::parse($start, $profile->timezone)->startOfDay()->utc();
        $endDate = Carbon::parse($end, $profile->timezone)->endOfDay()->utc();

        $bookings = $selectedAgenda
            ? $selectedAgenda->bookings()
                ->with('service:id,name')
                ->whereBetween('starts_at', [$startDate, $endDate])
                ->orderBy('starts_at')
                ->get()
                ->map(function ($booking) {
                    $zonedStart = $booking->starts_at->copy()->timezone($booking->timezone ?: config('app.timezone'));

                    $booking->setAttribute(
                        'can_manage',
                        $booking->starts_at->greaterThan(now()->addHours(2))
                    );
                    $booking->setAttribute('local_date_key', $zonedStart->format('Y-m-d'));
                    $booking->setAttribute('local_hour', (int) $zonedStart->format('H'));
                    $booking->setAttribute('local_time', $zonedStart->format('H:i'));

                    return $booking;
                })
            : [];

        return Inertia::render('Provider/Availability/Index', [
            'rules' => $selectedAgenda ? $selectedAgenda->availabilityRules()->orderBy('weekday')->get() : [],
            'bookings' => $bookings,
            'timezone' => $profile->timezone,
            'services' => $selectedAgenda
                ? $selectedAgenda->services()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'is_active', 'price_cents'])
                : [],
            'agendas' => $profile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published']),
            'selectedAgendaId' => $selectedAgenda?->id,
            'selectedRange' => [
                'start' => $start,
                'end' => $end,
            ],
            'today' => now($profile->timezone)->toDateString(),
        ]);
    }

    public function store(StoreAvailabilityRuleRequest $request): RedirectResponse
    {
        $payload = $request->validated();

        $request->user()->providerProfile->availabilityRules()->create($payload);

        return redirect()->route('availability-rules.index', ['agenda_id' => $payload['provider_agenda_id']])->with('status', 'rule-created');
    }

    public function update(UpdateAvailabilityRuleRequest $request, AvailabilityRule $availabilityRule): RedirectResponse
    {
        abort_unless($availabilityRule->provider_profile_id === $request->user()->providerProfile->id, 404);

        $payload = $request->validated();
        $availabilityRule->update($payload);

        return redirect()->route('availability-rules.index', ['agenda_id' => $availabilityRule->provider_agenda_id])->with('status', 'rule-updated');
    }

    public function destroy(Request $request, AvailabilityRule $availabilityRule): RedirectResponse
    {
        abort_unless($availabilityRule->provider_profile_id === $request->user()->providerProfile->id, 404);

        $agendaId = $availabilityRule->provider_agenda_id;
        $availabilityRule->delete();

        return redirect()->route('availability-rules.index', ['agenda_id' => $agendaId])->with('status', 'rule-deleted');
    }
}

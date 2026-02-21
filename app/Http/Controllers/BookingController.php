<?php

namespace App\Http\Controllers;

use App\BookingStatus;
use App\Http\Requests\UpdateProviderBookingRequest;
use App\Models\Booking;
use App\Models\Service;
use App\Support\AgendaContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request, AgendaContext $agendaContext): Response
    {
        $profile = $request->user()->providerProfile;
        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        return Inertia::render('Provider/Bookings/Index', [
            'bookings' => $selectedAgenda
                ? $selectedAgenda->bookings()->with(['service', 'payment'])->latest()->get()
                : [],
            'agendas' => $profile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published']),
            'selectedAgendaId' => $selectedAgenda?->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        $payload = $request->validate([
            'provider_agenda_id' => 'required|exists:provider_agendas,id',
            'service_id' => 'required|exists:services,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'starts_at' => 'required|date',
        ]);

        $payload['status'] = BookingStatus::Pending->value;

        $service = Service::query()
            ->where('provider_profile_id', $profile->id)
            ->where('provider_agenda_id', $payload['provider_agenda_id'])
            ->findOrFail($payload['service_id']);

        $startsAt = Carbon::parse($payload['starts_at'], $profile->timezone ?? config('app.timezone'))->utc();
        $endsAt = (clone $startsAt)->addMinutes($service->duration_minutes);

        // Checar conflitos simples
        $hasConflict = Booking::query()
            ->where('provider_agenda_id', $payload['provider_agenda_id'])
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'starts_at' => 'Já existe uma marcação nesse horário.',
            ]);
        }

        Booking::create([
            'provider_profile_id' => $profile->id,
            'provider_agenda_id' => $payload['provider_agenda_id'],
            'service_id' => $service->id,
            'customer_name' => $payload['customer_name'],
            'customer_email' => $payload['customer_email'],
            'customer_phone' => $payload['customer_phone'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $payload['status'],
            'timezone' => $profile->timezone ?? config('app.timezone'),
        ]);

        return back()->with('status', 'booking-created');
    }

    public function show(Request $request, Booking $booking): Response
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile && $booking->provider_profile_id === $profile->id, 404);

        $booking->load([
            'service',
            'payment',
            'customerFields',
            'attachments',
            'providerAgenda:id,name,slug',
        ]);

        return Inertia::render('Provider/Bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function update(UpdateProviderBookingRequest $request, Booking $booking): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile && $booking->provider_profile_id === $profile->id, 404);

        if (!$this->canManageBooking($booking)) {
            throw ValidationException::withMessages([
                'booking' => 'Só é possível alterar marcações futuras com pelo menos 2 horas de antecedência.',
            ]);
        }

        $payload = $request->validated();
        $service = Service::query()
            ->where('provider_profile_id', $profile->id)
            ->where('provider_agenda_id', $booking->provider_agenda_id)
            ->findOrFail($payload['service_id']);

        $startsAt = Carbon::createFromFormat(
            'Y-m-d H:i',
            $payload['starts_on'] . ' ' . $payload['starts_time'],
            $booking->timezone ?: ($profile->timezone ?? config('app.timezone'))
        )->utc();
        $endsAt = (clone $startsAt)->addMinutes($service->duration_minutes);

        $hasConflict = Booking::query()
            ->where('provider_profile_id', $profile->id)
            ->where('provider_agenda_id', $booking->provider_agenda_id)
            ->whereKeyNot($booking->id)
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'starts_time' => 'Já existe uma marcação nesse horário.',
            ]);
        }

        $booking->update([
            'service_id' => $service->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return back()->with('status', 'booking-updated');
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile && $booking->provider_profile_id === $profile->id, 404);

        $payload = $request->validate([
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->update([
            'status' => $payload['status'],
        ]);

        return back()->with('status', 'booking-status-updated');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile && $booking->provider_profile_id === $profile->id, 404);

        if (!$this->canManageBooking($booking)) {
            throw ValidationException::withMessages([
                'booking' => 'Só é possível excluir marcações futuras com pelo menos 2 horas de antecedência.',
            ]);
        }

        $booking->delete();

        return back()->with('status', 'booking-deleted');
    }

    private function canManageBooking(Booking $booking): bool
    {
        return $booking->starts_at->greaterThan(now()->addHours(2));
    }
}

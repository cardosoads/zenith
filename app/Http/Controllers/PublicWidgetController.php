<?php

namespace App\Http\Controllers;

use App\BookingSource;
use App\BookingStatus;
use App\Contracts\PixProviderInterface;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\ProviderAgenda;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Services\Booking\SlotService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicWidgetController extends Controller
{
    public function show(ProviderProfile $providerProfile, ProviderAgenda $agenda): Response
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id && $agenda->is_published,
            404
        );

        return Inertia::render('Widget/Show', [
            'provider' => $providerProfile->only(['id', 'slug', 'display_name', 'timezone']),
            'agenda' => $agenda,
            'services' => $agenda->services()->where('is_active', true)->get(),
        ]);
    }

    public function services(ProviderProfile $providerProfile, ProviderAgenda $agenda): JsonResponse
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id && $agenda->is_published,
            404
        );

        return response()->json(
            $agenda->services()->where('is_active', true)->get()
        );
    }

    public function availability(ProviderProfile $providerProfile, ProviderAgenda $agenda, SlotService $slotService): JsonResponse
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id && $agenda->is_published,
            404
        );

        $payload = request()->validate([
            'service_id' => ['required', 'exists:services,id'],
            'date' => ['required', 'date'],
        ]);

        $service = Service::query()
            ->where('provider_profile_id', $providerProfile->id)
            ->where('provider_agenda_id', $agenda->id)
            ->findOrFail($payload['service_id']);

        $slots = $slotService->getAvailableSlots(
            $providerProfile,
            $agenda,
            $service->duration_minutes,
            $service->break_minutes,
            $payload['date']
        );

        return response()->json($slots);
    }

    public function preview(StoreBookingRequest $request, ProviderProfile $providerProfile, ProviderAgenda $agenda): JsonResponse
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id && $agenda->is_published,
            404
        );

        $payload = $request->validated();

        $service = Service::query()
            ->where('provider_profile_id', $providerProfile->id)
            ->where('provider_agenda_id', $agenda->id)
            ->findOrFail($payload['service_id']);

        $requiresPayment = in_array($agenda->payment_requirement, ['full', 'half']) && $service->price_cents > 0;

        return response()->json([
            'provider' => $providerProfile->display_name,
            'agenda' => $agenda->only(['id', 'name', 'slug']),
            'service' => $service,
            'starts_at' => $payload['starts_at'],
            'price_cents' => $service->price_cents,
            'is_paid' => $requiresPayment,
        ]);
    }

    public function confirm(
        StoreBookingRequest $request,
        ProviderProfile $providerProfile,
        ProviderAgenda $agenda,
        PixProviderInterface $pixProvider
    ): JsonResponse {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id && $agenda->is_published,
            404
        );

        $payload = $request->validated();

        $service = Service::query()
            ->where('provider_profile_id', $providerProfile->id)
            ->where('provider_agenda_id', $agenda->id)
            ->findOrFail($payload['service_id']);

        $booking = DB::transaction(function () use ($request, $payload, $providerProfile, $agenda, $service, $pixProvider) {
            $start = Carbon::parse($payload['starts_at'])->utc();
            $end = $start->copy()->addMinutes($service->duration_minutes);

            $hasConflict = Booking::query()
                ->where('provider_profile_id', $providerProfile->id)
                ->where('provider_agenda_id', $agenda->id)
                ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->lockForUpdate()
                ->exists();

            abort_if($hasConflict, 409, 'Horario indisponivel.');

            $requiresPayment = in_array($agenda->payment_requirement, ['full', 'half']) && $service->price_cents > 0;

            $booking = Booking::query()->create([
                'provider_profile_id' => $providerProfile->id,
                'provider_agenda_id' => $agenda->id,
                'service_id' => $service->id,
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'] ?? null,
                'customer_notes' => $payload['customer_notes'] ?? null,
                'starts_at' => $start,
                'ends_at' => $end,
                'timezone' => $providerProfile->timezone,
                'status' => $requiresPayment ? BookingStatus::Pending : BookingStatus::Confirmed,
                'source' => BookingSource::Widget,
            ]);

            foreach (($payload['extra_fields'] ?? []) as $extraField) {
                $booking->customerFields()->create($extraField);
            }

            foreach (($request->file('attachments') ?? []) as $attachmentFile) {
                $path = $attachmentFile->storeAs(
                    'booking-attachments/' . $booking->id,
                    Str::uuid() . '-' . $attachmentFile->getClientOriginalName(),
                    'public'
                );

                $booking->attachments()->create([
                    'path' => $path,
                    'original_name' => $attachmentFile->getClientOriginalName(),
                    'mime_type' => $attachmentFile->getClientMimeType(),
                    'size_bytes' => $attachmentFile->getSize() ?: 0,
                ]);
            }

            if ($requiresPayment) {
                $charge = $pixProvider->createCharge($booking);

                BookingPayment::query()->create([
                    'booking_id' => $booking->id,
                    'method' => 'pix',
                    'provider' => 'asaas',
                    'external_id' => $charge['external_id'],
                    'qr_code_text' => $charge['qr_code_text'],
                    'qr_code_image_url' => $charge['qr_code_image_url'],
                    'status' => $charge['status'],
                ]);
            }

            return $booking->load(['service', 'payment', 'customerFields', 'attachments']);
        });

        return response()->json($booking, 201);
    }

    public function payment(ProviderProfile $providerProfile, ProviderAgenda $agenda, Booking $booking): JsonResponse
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id
            && $booking->provider_profile_id === $providerProfile->id
            && $booking->provider_agenda_id === $agenda->id,
            404
        );

        return response()->json($booking->load('payment'));
    }

    public function search(ProviderProfile $providerProfile, ProviderAgenda $agenda): JsonResponse
    {
        $payload = request()->validate([
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
        ]);

        if (empty($payload['email']) && empty($payload['phone'])) {
            return response()->json(['message' => 'Informe o e-mail ou telefone.'], 422);
        }

        $query = $agenda->bookings()
            ->with(['service', 'payment'])
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');

        if (!empty($payload['email'])) {
            $query->where('customer_email', $payload['email']);
        } elseif (!empty($payload['phone'])) {
            $query->where('customer_phone', $payload['phone']);
        }

        return response()->json($query->get());
    }

    public function cancel(ProviderProfile $providerProfile, ProviderAgenda $agenda, Booking $booking): JsonResponse
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id
            && $booking->provider_profile_id === $providerProfile->id
            && $booking->provider_agenda_id === $agenda->id,
            404
        );

        if (!$this->canManageBooking($booking)) {
            return response()->json(['message' => 'Só é possível cancelar com pelo menos 2 horas de antecedência.'], 422);
        }

        $booking->update(['status' => BookingStatus::Cancelled]);

        return response()->json(['message' => 'Agendamento cancelado com sucesso.']);
    }

    public function reschedule(ProviderProfile $providerProfile, ProviderAgenda $agenda, Booking $booking): JsonResponse
    {
        abort_unless(
            $agenda->provider_profile_id === $providerProfile->id
            && $booking->provider_profile_id === $providerProfile->id
            && $booking->provider_agenda_id === $agenda->id,
            404
        );

        if (!$this->canManageBooking($booking)) {
            return response()->json(['message' => 'Só é possível remarcar com pelo menos 2 horas de antecedência.'], 422);
        }

        $payload = request()->validate([
            'starts_at' => ['required', 'date', 'after:now'],
        ]);

        $start = Carbon::parse($payload['starts_at'])->utc();
        $end = $start->copy()->addMinutes($booking->service->duration_minutes);

        $hasConflict = Booking::query()
            ->where('provider_profile_id', $providerProfile->id)
            ->where('provider_agenda_id', $agenda->id)
            ->where('id', '!=', $booking->id)
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();

        if ($hasConflict) {
            return response()->json(['message' => 'Horário indisponível para o reagendamento.'], 409);
        }

        $booking->update([
            'starts_at' => $start,
            'ends_at' => $end,
        ]);

        return response()->json(['message' => 'Agendamento remarcado com sucesso.']);
    }

    private function canManageBooking(Booking $booking): bool
    {
        return $booking->starts_at->greaterThan(now()->addHours(2));
    }
}

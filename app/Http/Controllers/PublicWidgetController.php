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

        return response()->json([
            'provider' => $providerProfile->display_name,
            'agenda' => $agenda->only(['id', 'name', 'slug']),
            'service' => $service,
            'starts_at' => $payload['starts_at'],
            'price_cents' => $service->price_cents,
            'is_paid' => $service->price_cents > 0,
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
                'status' => BookingStatus::Pending,
                'source' => BookingSource::Widget,
            ]);

            foreach (($payload['extra_fields'] ?? []) as $extraField) {
                $booking->customerFields()->create($extraField);
            }

            foreach (($request->file('attachments') ?? []) as $attachmentFile) {
                $path = $attachmentFile->storeAs(
                    'booking-attachments/'.$booking->id,
                    Str::uuid().'-'.$attachmentFile->getClientOriginalName(),
                    'public'
                );

                $booking->attachments()->create([
                    'path' => $path,
                    'original_name' => $attachmentFile->getClientOriginalName(),
                    'mime_type' => $attachmentFile->getClientMimeType(),
                    'size_bytes' => $attachmentFile->getSize() ?: 0,
                ]);
            }

            if ($service->price_cents > 0) {
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
            } else {
                $booking->update(['status' => BookingStatus::Confirmed]);
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
}

<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('provider');
    }

    public function rules(): array
    {
        /** @var Booking|null $booking */
        $booking = $this->route('booking');
        $profileId = $this->user()?->providerProfile?->id;
        $agendaId = $booking?->provider_agenda_id;

        return [
            'service_id' => [
                'required',
                'integer',
                Rule::exists('services', 'id')
                    ->where('provider_profile_id', $profileId)
                    ->where('provider_agenda_id', $agendaId),
            ],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'starts_time' => ['required', 'date_format:H:i'],
        ];
    }
}

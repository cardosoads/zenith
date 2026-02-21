<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAvailabilityRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('provider');
    }

    public function rules(): array
    {
        $providerProfileId = $this->user()?->providerProfile?->id;

        return [
            'provider_agenda_id' => [
                'required',
                'integer',
                Rule::exists('provider_agendas', 'id')->where('provider_profile_id', $providerProfileId),
            ],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

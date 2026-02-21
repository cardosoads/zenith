<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'categories' => ['nullable', 'array'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'break_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('provider');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'primaryColor' => ['nullable', 'string', 'max:20'],
            'secondaryColor' => ['nullable', 'string', 'max:20'],
            'interval' => ['required', 'integer', 'min:1', 'max:480'],
            'weekdays' => ['required', 'array'],
            'weekdays.*' => ['string', 'in:seg,ter,qua,qui,sex,sab,dom'],
            'startTime' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'endTime' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'services' => ['required', 'array', 'min:1'],
            'services.*.name' => ['required', 'string', 'max:120'],
            'services.*.price' => ['nullable', 'string'],
            'services.*.isFree' => ['required', 'boolean'],
            'payment_requirement' => ['required', 'string', 'in:none,full,half'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}

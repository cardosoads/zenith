<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('provider');
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:120'],
            'timezone' => ['required', 'timezone'],
            'cancellation_cutoff_hours' => ['required', 'integer', 'between:1,168'],
            'pix_key' => ['nullable', 'string', 'max:160'],
        ];
    }
}

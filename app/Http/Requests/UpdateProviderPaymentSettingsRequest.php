<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderPaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('provider');
    }

    public function rules(): array
    {
        return [
            'pix_key' => ['required', 'string', 'max:160'],
            'pix_key_type' => ['required', 'in:cpf,cnpj,email,phone,random'],
            'pix_holder_name' => ['required', 'string', 'max:120'],
            'pix_holder_document' => ['required', 'string', 'max:20'],
        ];
    }
}

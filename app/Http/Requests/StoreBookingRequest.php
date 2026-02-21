<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:160'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
            'extra_fields' => ['nullable', 'array', 'max:3'],
            'extra_fields.*.field_key' => ['required', 'string', 'max:50'],
            'extra_fields.*.field_label' => ['required', 'string', 'max:80'],
            'extra_fields.*.field_value' => ['nullable', 'string', 'max:255'],
            'theme' => ['nullable', 'in:light,dark,auto'],
            'accent' => ['nullable', 'in:sky,honey,green'],
            'density' => ['nullable', 'in:comfortable,medium'],
            'preset' => ['nullable', 'in:clean,contrast,soft,editorial'],
        ];
    }
}

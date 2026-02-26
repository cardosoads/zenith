<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'email_new_booking' => ['required', 'boolean'],
            'email_cancellation' => ['required', 'boolean'],
            'email_reminders' => ['required', 'boolean'],
            'whatsapp_confirmation' => ['required', 'boolean'],
            'whatsapp_reminder_24h' => ['required', 'boolean'],
            'whatsapp_cancellation' => ['required', 'boolean'],
        ];
    }
}

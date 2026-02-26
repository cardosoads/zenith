<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'email_new_booking',
        'email_cancellation',
        'email_reminders',
        'whatsapp_confirmation',
        'whatsapp_reminder_24h',
        'whatsapp_cancellation',
    ];

    protected function casts(): array
    {
        return [
            'email_new_booking' => 'boolean',
            'email_cancellation' => 'boolean',
            'email_reminders' => 'boolean',
            'whatsapp_confirmation' => 'boolean',
            'whatsapp_reminder_24h' => 'boolean',
            'whatsapp_cancellation' => 'boolean',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}

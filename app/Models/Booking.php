<?php

namespace App\Models;

use App\BookingSource;
use App\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'provider_agenda_id',
        'service_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_notes',
        'starts_at',
        'ends_at',
        'timezone',
        'status',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => BookingStatus::class,
            'source' => BookingSource::class,
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function providerAgenda(): BelongsTo
    {
        return $this->belongsTo(ProviderAgenda::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function customerFields(): HasMany
    {
        return $this->hasMany(BookingCustomerField::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BookingAttachment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(BookingPayment::class);
    }
}

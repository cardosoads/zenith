<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'provider_agenda_id',
        'name',
        'categories',
        'description',
        'duration_minutes',
        'break_minutes',
        'price_cents',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'break_minutes' => 'integer',
            'price_cents' => 'integer',
            'is_active' => 'boolean',
            'categories' => 'array',
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}

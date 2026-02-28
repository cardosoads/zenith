<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProviderAgenda extends Model
{
    /** @use HasFactory<\Database\Factories\ProviderAgendaFactory> */
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'name',
        'slug',
        'description',
        'timezone',
        'is_published',
        'theme',
        'accent',
        'density',
        'preset',
        'customer_extra_fields',
        'embed_height',
        'embed_width',
        'transparent_bg',
        'primary_color',
        'secondary_color',
        'payment_requirement',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'customer_extra_fields' => 'array',
            'embed_height' => 'integer',
            'embed_width' => 'integer',
            'transparent_bg' => 'boolean',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function availabilityRules(): HasMany
    {
        return $this->hasMany(AvailabilityRule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(Unavailability::class);
    }

    public function widgetConfig(): HasOne
    {
        return $this->hasOne(WidgetConfig::class);
    }
}

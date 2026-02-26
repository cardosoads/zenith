<?php

namespace App\Models;

use App\ProviderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'display_name',
        'segment',
        'cnpj',
        'address',
        'logo_path',
        'timezone',
        'currency',
        'status',
        'billing_status',
        'trial_ends_at',
        'cancellation_cutoff_hours',
        'pix_key',
        'pix_key_type',
        'pix_holder_name',
        'pix_holder_document',
        'stripe_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProviderStatus::class,
            'cancellation_cutoff_hours' => 'integer',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ProviderSubscription::class, 'provider_profile_id');
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(ProviderSubscription::class, 'provider_profile_id')->latestOfMany();
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(ProviderAgenda::class, 'provider_profile_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'provider_profile_id');
    }

    public function availabilityRules(): HasMany
    {
        return $this->hasMany(AvailabilityRule::class, 'provider_profile_id');
    }

    public function unavailabilities(): HasMany
    {
        return $this->hasMany(Unavailability::class, 'provider_profile_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_profile_id');
    }

    public function latestAgenda(): HasOne
    {
        return $this->hasOne(ProviderAgenda::class, 'provider_profile_id')->latestOfMany();
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function notificationPreference(): HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function trialExpired(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isPast();
    }
}

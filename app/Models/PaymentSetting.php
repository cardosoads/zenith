<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    protected $fillable = [
        'stripe_public_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'card_enabled',
        'pix_enabled',
        'is_live_mode',
    ];

    protected function casts(): array
    {
        return [
            'stripe_public_key' => 'encrypted',
            'stripe_secret_key' => 'encrypted',
            'stripe_webhook_secret' => 'encrypted',
            'card_enabled' => 'boolean',
            'pix_enabled' => 'boolean',
            'is_live_mode' => 'boolean',
        ];
    }

    public static function instance(): static
    {
        return static::query()->firstOrCreate([], [
            'card_enabled' => true,
            'pix_enabled' => true,
            'is_live_mode' => false,
        ]);
    }

    public function hasStripeKeys(): bool
    {
        return $this->stripe_public_key && $this->stripe_secret_key;
    }
}

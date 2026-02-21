<?php

namespace Database\Factories;

use App\Models\User;
use App\ProviderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderProfile>
 */
class ProviderProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'slug' => 'provider-'.Str::lower(Str::random(8)),
            'display_name' => fake()->company(),
            'timezone' => 'America/Sao_Paulo',
            'status' => ProviderStatus::Active,
            'billing_status' => 'active',
            'cancellation_cutoff_hours' => 24,
            'pix_key' => null,
            'pix_key_type' => null,
            'pix_holder_name' => null,
            'pix_holder_document' => null,
        ];
    }
}

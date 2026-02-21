<?php

namespace Database\Factories;

use App\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderAgenda>
 */
class ProviderAgendaFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'provider_profile_id' => ProviderProfile::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(100, 999)),
            'description' => fake()->sentence(),
            'timezone' => 'America/Sao_Paulo',
            'is_published' => false,
            'theme' => 'auto',
            'accent' => 'sky',
            'density' => 'medium',
            'preset' => 'clean',
            'customer_extra_fields' => [],
            'embed_height' => 680,
        ];
    }
}

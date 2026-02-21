<?php

use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Spatie\Permission\Models\Role;

test('provider without active subscription is redirected to onboarding', function () {
    Role::findOrCreate('provider');

    $user = User::factory()->create();
    $user->assignRole('provider');

    ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'provider-pending',
        'display_name' => 'Provider Pending',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Pending,
        'billing_status' => 'pending',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('onboarding.show'));
});

test('provider with active subscription can access dashboard', function () {
    Role::findOrCreate('provider');

    $plan = Plan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter-test',
        'price_cents' => 4900,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('provider');

    $profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'provider-active',
        'display_name' => 'Provider Active',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Active,
        'billing_status' => 'active',
    ]);

    ProviderSubscription::query()->create([
        'provider_profile_id' => $profile->id,
        'plan_id' => $plan->id,
        'payment_provider' => 'abacate',
        'status' => SubscriptionStatus::Active,
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
});

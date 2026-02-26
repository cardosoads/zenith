<?php

use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\ProviderStatus;
use App\SubscriptionStatus;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Plan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter',
        'price_cents' => 4900,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'cpf' => '123.456.789-00',
        'phone' => '(11) 99999-0000',
        'seguimento' => 'Beleza',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $profile = ProviderProfile::query()->first();

    expect($profile)->not->toBeNull()
        ->and($profile->status)->toBe(ProviderStatus::Active)
        ->and($profile->billing_status)->toBe('trialing')
        ->and($profile->trial_ends_at)->not->toBeNull();

    $subscription = ProviderSubscription::query()->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->status)->toBe(SubscriptionStatus::Trialing)
        ->and($subscription->payment_provider)->toBe('trial');
});

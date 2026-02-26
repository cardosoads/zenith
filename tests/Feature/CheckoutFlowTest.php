<?php

use App\Contracts\BillingProviderInterface;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin');
    Role::findOrCreate('provider');

    $this->plan = Plan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter',
        'price_cents' => 7900,
        'billing_cycle' => 'monthly',
        'is_active' => true,
        'stripe_price_id' => 'price_test_123',
    ]);

    $this->mock(BillingProviderInterface::class, function ($mock) {
        $mock->shouldReceive('createSubscription')
            ->andReturn([
                'client_secret' => 'pi_test_secret_123',
                'external_id' => 'sub_test_123',
                'status' => 'incomplete',
                'customer_id' => 'cus_test_123',
            ]);
    });
});

test('checkout page loads with plans', function () {
    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Checkout')
            ->has('plans')
        );
});

test('new user can create account and subscription via checkout', function () {
    $response = $this->postJson(route('checkout.store'), [
        'plan_id' => $this->plan->id,
        'first_name' => 'João',
        'last_name' => 'Silva',
        'email' => 'joao@example.com',
        'document' => '123.456.789-00',
        'phone' => '(11) 99999-9999',
        'business_name' => 'Studio João',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['client_secret', 'subscription_id']);

    $user = User::query()->where('email', 'joao@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('João Silva')
        ->and($user->cpf)->toBe('123.456.789-00')
        ->and($user->phone)->toBe('(11) 99999-9999')
        ->and($user->hasRole('provider'))->toBeTrue();

    $profile = $user->providerProfile;
    expect($profile)->not->toBeNull()
        ->and($profile->display_name)->toBe('Studio João');

    $subscription = $profile->currentSubscription;
    expect($subscription)->not->toBeNull()
        ->and($subscription->external_id)->toBe('sub_test_123')
        ->and($subscription->payment_provider)->toBe('stripe');
});

test('checkout store validates required fields', function () {
    $this->postJson(route('checkout.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['plan_id', 'first_name', 'last_name', 'email', 'document', 'phone', 'business_name', 'password']);
});

test('checkout store rejects duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson(route('checkout.store'), [
        'plan_id' => $this->plan->id,
        'first_name' => 'João',
        'last_name' => 'Silva',
        'email' => 'existing@example.com',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('checkout store rejects duplicate cpf and phone', function () {
    User::factory()->create([
        'cpf' => '123.456.789-00',
        'phone' => '(11) 99999-9999',
    ]);

    $this->postJson(route('checkout.store'), [
        'plan_id' => $this->plan->id,
        'first_name' => 'João',
        'last_name' => 'Silva',
        'email' => 'novo@example.com',
        'document' => '123.456.789-00',
        'phone' => '(11) 99999-9999',
        'business_name' => 'Studio João',
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['document', 'phone']);
});

test('checkout confirm activates subscription', function () {
    $user = User::factory()->create();
    $user->assignRole('provider');

    $profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'test-provider',
        'display_name' => 'Test Provider',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Pending,
        'billing_status' => 'pending',
    ]);

    ProviderSubscription::query()->create([
        'provider_profile_id' => $profile->id,
        'plan_id' => $this->plan->id,
        'payment_provider' => 'stripe',
        'external_id' => 'sub_test_123',
        'status' => SubscriptionStatus::Pending,
    ]);

    $this->actingAs($user)
        ->post(route('checkout.confirm'))
        ->assertRedirect(route('dashboard'));

    $profile->refresh();
    expect($profile->status)->toBe(ProviderStatus::Active)
        ->and($profile->billing_status)->toBe('active');

    $subscription = $profile->currentSubscription;
    expect($subscription->status)->toBe(SubscriptionStatus::Active);
});

test('onboarding checkout returns client secret for authenticated user', function () {
    $user = User::factory()->create();
    $user->assignRole('provider');

    $response = $this->actingAs($user)
        ->postJson(route('onboarding.checkout'), [
            'plan_id' => $this->plan->id,
        ]);

    $response->assertOk()
        ->assertJsonStructure(['client_secret', 'subscription_id']);

    $profile = $user->fresh()->providerProfile;
    expect($profile)->not->toBeNull();

    $subscription = $profile->currentSubscription;
    expect($subscription)->not->toBeNull()
        ->and($subscription->external_id)->toBe('sub_test_123');
});

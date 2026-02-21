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
    Role::findOrCreate('provider');

    $this->plan = Plan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter-webhook',
        'price_cents' => 7900,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('provider');

    $this->profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'webhook-test-provider',
        'display_name' => 'Webhook Test',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Pending,
        'billing_status' => 'pending',
    ]);

    $this->subscription = ProviderSubscription::query()->create([
        'provider_profile_id' => $this->profile->id,
        'plan_id' => $this->plan->id,
        'payment_provider' => 'stripe',
        'external_id' => 'sub_webhook_123',
        'status' => SubscriptionStatus::Pending,
    ]);
});

test('webhook activates subscription on payment succeeded', function () {
    $this->mock(BillingProviderInterface::class, function ($mock) {
        $mock->shouldReceive('parseWebhookPayload')
            ->andReturn([
                'event_type' => 'invoice.payment_succeeded',
                'external_id' => 'sub_webhook_123',
                'status' => 'active',
            ]);
    });

    $this->postJson(route('webhooks.billing', 'stripe'), [
        'type' => 'invoice.payment_succeeded',
    ])->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->status)->toBe(SubscriptionStatus::Active);

    $this->profile->refresh();
    expect($this->profile->status)->toBe(ProviderStatus::Active)
        ->and($this->profile->billing_status)->toBe('active');
});

test('webhook marks subscription as past due on payment failed', function () {
    $this->mock(BillingProviderInterface::class, function ($mock) {
        $mock->shouldReceive('parseWebhookPayload')
            ->andReturn([
                'event_type' => 'invoice.payment_failed',
                'external_id' => 'sub_webhook_123',
                'status' => 'past_due',
            ]);
    });

    $this->postJson(route('webhooks.billing', 'stripe'), [
        'type' => 'invoice.payment_failed',
    ])->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->status)->toBe(SubscriptionStatus::PastDue);
});

test('webhook cancels subscription on deletion', function () {
    $this->subscription->update(['status' => SubscriptionStatus::Active]);

    $this->mock(BillingProviderInterface::class, function ($mock) {
        $mock->shouldReceive('parseWebhookPayload')
            ->andReturn([
                'event_type' => 'customer.subscription.deleted',
                'external_id' => 'sub_webhook_123',
                'status' => 'cancelled',
            ]);
    });

    $this->postJson(route('webhooks.billing', 'stripe'), [
        'type' => 'customer.subscription.deleted',
    ])->assertOk();

    $this->subscription->refresh();
    expect($this->subscription->status)->toBe(SubscriptionStatus::Cancelled);
});

test('webhook returns ok for unknown subscription', function () {
    $this->mock(BillingProviderInterface::class, function ($mock) {
        $mock->shouldReceive('parseWebhookPayload')
            ->andReturn([
                'event_type' => 'invoice.payment_succeeded',
                'external_id' => 'sub_nonexistent',
                'status' => 'active',
            ]);
    });

    $this->postJson(route('webhooks.billing', 'stripe'), [])
        ->assertOk();
});

<?php

use App\Models\PaymentSetting;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin');
    Role::findOrCreate('provider');
});

test('guest cannot access payment settings', function () {
    $this->get(route('admin.payment-settings.index'))
        ->assertRedirect(route('login'));
});

test('non-admin cannot access payment settings', function () {
    $user = User::factory()->create();
    $user->assignRole('provider');

    $this->actingAs($user)
        ->get(route('admin.payment-settings.index'))
        ->assertForbidden();
});

test('admin can view payment settings page', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('admin.payment-settings.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/PaymentSettings/Index')
            ->has('settings')
        );
});

test('admin can update payment settings', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->patch(route('admin.payment-settings.update'), [
            'stripe_public_key' => 'pk_test_123456789',
            'stripe_secret_key' => 'sk_test_123456789',
            'stripe_webhook_secret' => 'whsec_test_123',
            'card_enabled' => true,
            'pix_enabled' => false,
        ])
        ->assertRedirect(route('admin.payment-settings.index'));

    $settings = PaymentSetting::instance();
    expect($settings->stripe_public_key)->toBe('pk_test_123456789')
        ->and($settings->stripe_secret_key)->toBe('sk_test_123456789')
        ->and($settings->card_enabled)->toBeTrue()
        ->and($settings->pix_enabled)->toBeFalse();
});

test('admin can update toggles without changing keys', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $settings = PaymentSetting::instance();
    $settings->update([
        'stripe_public_key' => 'pk_test_original',
        'stripe_secret_key' => 'sk_test_original',
    ]);

    $this->actingAs($user)
        ->patch(route('admin.payment-settings.update'), [
            'stripe_public_key' => '',
            'stripe_secret_key' => '',
            'stripe_webhook_secret' => '',
            'card_enabled' => false,
            'pix_enabled' => true,
        ])
        ->assertRedirect();

    $settings->refresh();
    expect($settings->stripe_public_key)->toBe('pk_test_original')
        ->and($settings->card_enabled)->toBeFalse()
        ->and($settings->pix_enabled)->toBeTrue();
});

test('non-admin cannot update payment settings', function () {
    $user = User::factory()->create();
    $user->assignRole('provider');

    $this->actingAs($user)
        ->patch(route('admin.payment-settings.update'), [
            'card_enabled' => true,
            'pix_enabled' => true,
        ])
        ->assertForbidden();
});

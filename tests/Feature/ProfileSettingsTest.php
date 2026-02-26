<?php

use App\Models\ProviderProfile;
use App\Models\TeamMember;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

function createProviderUser(): User
{
    $user = User::factory()->create();

    ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => fake()->unique()->slug(),
        'display_name' => fake()->company(),
        'timezone' => 'America/Sao_Paulo',
        'billing_status' => 'trialing',
    ]);

    return $user;
}

test('business settings can be updated', function () {
    $user = createProviderUser();

    $response = actingAs($user)
        ->from('/profile')
        ->patch(route('profile.business.update'), [
            'display_name' => 'Clinica Zenith',
            'cnpj' => '12.345.678/0001-90',
            'phone' => '+55 11 99999-9999',
            'address' => 'Rua das Flores, 100',
            'timezone' => 'America/Sao_Paulo',
            'currency' => 'BRL',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    assertDatabaseHas('provider_profiles', [
        'id' => $user->providerProfile->id,
        'display_name' => 'Clinica Zenith',
        'cnpj' => '12.345.678/0001-90',
        'address' => 'Rua das Flores, 100',
        'timezone' => 'America/Sao_Paulo',
        'currency' => 'BRL',
    ]);

    assertDatabaseHas('users', [
        'id' => $user->id,
        'phone' => '+55 11 99999-9999',
    ]);
});

test('team member can be created updated and deleted', function () {
    $user = createProviderUser();

    $storeResponse = actingAs($user)
        ->from('/profile')
        ->post(route('profile.team.store'), [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'role' => 'profissional',
        ]);

    $storeResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $teamMember = TeamMember::query()->where('email', 'maria@example.com')->firstOrFail();

    assertDatabaseHas('team_members', [
        'id' => $teamMember->id,
        'provider_profile_id' => $user->providerProfile->id,
        'name' => 'Maria Silva',
        'role' => 'profissional',
        'is_active' => 1,
    ]);

    $updateResponse = actingAs($user)
        ->from('/profile')
        ->patch(route('profile.team.update', $teamMember), [
            'name' => 'Maria Souza',
            'email' => 'maria.souza@example.com',
            'role' => 'admin',
            'is_active' => false,
        ]);

    $updateResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    assertDatabaseHas('team_members', [
        'id' => $teamMember->id,
        'provider_profile_id' => $user->providerProfile->id,
        'name' => 'Maria Souza',
        'email' => 'maria.souza@example.com',
        'role' => 'admin',
        'is_active' => 0,
    ]);

    $deleteResponse = actingAs($user)
        ->from('/profile')
        ->delete(route('profile.team.destroy', $teamMember));

    $deleteResponse
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    assertDatabaseMissing('team_members', [
        'id' => $teamMember->id,
    ]);
});

test('team member updates are scoped to the authenticated provider profile', function () {
    $owner = createProviderUser();
    $otherUser = createProviderUser();

    $teamMember = TeamMember::query()->create([
        'provider_profile_id' => $owner->providerProfile->id,
        'name' => 'Aline Costa',
        'email' => 'aline@example.com',
        'role' => 'profissional',
        'is_active' => true,
    ]);

    $response = actingAs($otherUser)
        ->patch(route('profile.team.update', $teamMember), [
            'name' => 'Tentativa',
            'email' => 'tentativa@example.com',
            'role' => 'admin',
            'is_active' => false,
        ]);

    $response->assertForbidden();

    assertDatabaseHas('team_members', [
        'id' => $teamMember->id,
        'name' => 'Aline Costa',
        'email' => 'aline@example.com',
        'role' => 'profissional',
        'is_active' => 1,
    ]);
});

test('notification preferences can be updated', function () {
    $user = createProviderUser();

    $response = actingAs($user)
        ->from('/profile')
        ->patch(route('profile.notifications.update'), [
            'email_new_booking' => true,
            'email_cancellation' => false,
            'email_reminders' => true,
            'whatsapp_confirmation' => false,
            'whatsapp_reminder_24h' => true,
            'whatsapp_cancellation' => false,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    assertDatabaseHas('notification_preferences', [
        'provider_profile_id' => $user->providerProfile->id,
        'email_new_booking' => 1,
        'email_cancellation' => 0,
        'email_reminders' => 1,
        'whatsapp_confirmation' => 0,
        'whatsapp_reminder_24h' => 1,
        'whatsapp_cancellation' => 0,
    ]);
});

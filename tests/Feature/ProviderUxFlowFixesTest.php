<?php

use App\Models\Booking;
use App\Models\Plan;
use App\Models\ProviderAgenda;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\Service;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

function makeActiveProviderUser(): User
{
    Role::findOrCreate('provider');

    $plan = Plan::query()->firstOrCreate([
        'slug' => 'starter-provider-ux-test',
    ], [
        'name' => 'Starter UX',
        'price_cents' => 4900,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('provider');

    $profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'provider-ux-'.str()->lower(str()->random(8)),
        'display_name' => 'Provider UX',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Active,
        'billing_status' => 'active',
    ]);

    ProviderAgenda::query()->create([
        'provider_profile_id' => $profile->id,
        'name' => 'Agenda Teste',
        'slug' => 'agenda-teste',
        'timezone' => 'America/Sao_Paulo',
        'is_published' => false,
        'theme' => 'auto',
        'accent' => 'sky',
        'density' => 'medium',
        'preset' => 'clean',
        'embed_height' => 680,
        'customer_extra_fields' => [],
    ]);

    ProviderSubscription::query()->create([
        'provider_profile_id' => $profile->id,
        'plan_id' => $plan->id,
        'payment_provider' => 'abacate',
        'external_id' => 'ext-'.str()->uuid(),
        'status' => SubscriptionStatus::Active,
        'current_period_start' => now(),
        'current_period_end' => now()->addMonth(),
    ]);

    return $user->refresh();
}

test('service paid requires pix configuration', function () {
    $user = makeActiveProviderUser();
    $agendaId = $user->providerProfile->agendas()->value('id');

    $response = $this->actingAs($user)->post(route('services.store'), [
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico Pago',
        'description' => 'Descricao',
        'duration_minutes' => 60,
        'break_minutes' => 10,
        'price_cents' => 15000,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('price_cents');

    $this->assertDatabaseMissing('services', [
        'provider_profile_id' => $user->providerProfile->id,
        'name' => 'Servico Pago',
    ]);
});

test('service can be updated from existing record', function () {
    $user = makeActiveProviderUser();
    $agendaId = $user->providerProfile->agendas()->value('id');

    $user->providerProfile->update([
        'pix_key' => 'contato@pix.test',
        'pix_key_type' => 'email',
        'pix_holder_name' => 'Provider UX',
        'pix_holder_document' => '12345678901',
    ]);

    $service = Service::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico Inicial',
        'description' => 'Antes',
        'duration_minutes' => 45,
        'break_minutes' => 5,
        'price_cents' => 10000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->put(route('services.update', $service), [
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico Editado',
        'description' => 'Depois',
        'duration_minutes' => 90,
        'break_minutes' => 10,
        'price_cents' => 22000,
        'is_active' => false,
    ]);

    $response->assertRedirect(route('services.index', ['agenda_id' => $agendaId]));

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Servico Editado',
        'duration_minutes' => 90,
        'break_minutes' => 10,
        'price_cents' => 22000,
        'is_active' => 0,
    ]);
});

test('payment settings can be updated', function () {
    $user = makeActiveProviderUser();

    $response = $this->actingAs($user)->patch(route('provider.payment-settings.update'), [
        'pix_key' => '11988887777',
        'pix_key_type' => 'phone',
        'pix_holder_name' => 'Empresa Teste',
        'pix_holder_document' => '11222333000199',
    ]);

    $response->assertRedirect(route('provider.payment-settings.index'));

    $this->assertDatabaseHas('provider_profiles', [
        'id' => $user->providerProfile->id,
        'pix_key' => '11988887777',
        'pix_key_type' => 'phone',
        'pix_holder_name' => 'Empresa Teste',
        'pix_holder_document' => '11222333000199',
    ]);
});

test('availability page returns calendar range data', function () {
    $user = makeActiveProviderUser();
    $agendaId = $user->providerProfile->agendas()->value('id');

    $service = Service::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico Agenda',
        'duration_minutes' => 30,
        'break_minutes' => 5,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $bookingStart = now('America/Sao_Paulo')->addDay()->setTime(10, 0)->utc();

    $user->providerProfile->bookings()->create([
        'provider_agenda_id' => $agendaId,
        'service_id' => $service->id,
        'customer_name' => 'Cliente Agenda',
        'customer_email' => 'agenda@example.com',
        'starts_at' => $bookingStart,
        'ends_at' => $bookingStart->copy()->addMinutes(30),
        'timezone' => 'America/Sao_Paulo',
        'status' => 'confirmed',
        'source' => 'dashboard',
    ]);

    $response = $this->actingAs($user)->get(route('availability-rules.index', [
        'agenda_id' => $agendaId,
        'start' => now('America/Sao_Paulo')->toDateString(),
        'end' => now('America/Sao_Paulo')->addDays(7)->toDateString(),
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Provider/Availability/Index')
        ->has('bookings', 1)
        ->has('selectedRange.start')
        ->has('selectedRange.end')
    );
});

test('provider can update future booking with two hours minimum lead time', function () {
    $user = makeActiveProviderUser();
    $agendaId = $user->providerProfile->agendas()->value('id');

    $serviceA = Service::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico A',
        'duration_minutes' => 30,
        'break_minutes' => 5,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $serviceB = Service::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico B',
        'duration_minutes' => 60,
        'break_minutes' => 5,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $startsAt = now('America/Sao_Paulo')->addDay()->setTime(10, 0)->utc();
    $booking = Booking::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'service_id' => $serviceA->id,
        'customer_name' => 'Cliente Atualizar',
        'customer_email' => 'cliente.update@example.com',
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(30),
        'timezone' => 'America/Sao_Paulo',
        'status' => 'confirmed',
        'source' => 'dashboard',
    ]);

    $response = $this->actingAs($user)->put(route('bookings.update', $booking), [
        'service_id' => $serviceB->id,
        'starts_on' => now('America/Sao_Paulo')->addDays(1)->toDateString(),
        'starts_time' => '15:30',
    ]);

    $response->assertSessionDoesntHaveErrors();

    $booking->refresh();

    expect($booking->service_id)->toBe($serviceB->id);
    expect($booking->starts_at->timezone('America/Sao_Paulo')->format('H:i'))->toBe('15:30');
});

test('provider cannot update booking within two hours', function () {
    $user = makeActiveProviderUser();
    $agendaId = $user->providerProfile->agendas()->value('id');

    $service = Service::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'name' => 'Servico Restrito',
        'duration_minutes' => 30,
        'break_minutes' => 5,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $startsAt = now('America/Sao_Paulo')->addMinutes(90)->utc();
    $booking = Booking::query()->create([
        'provider_profile_id' => $user->providerProfile->id,
        'provider_agenda_id' => $agendaId,
        'service_id' => $service->id,
        'customer_name' => 'Cliente Restrito',
        'customer_email' => 'cliente.restrito@example.com',
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addMinutes(30),
        'timezone' => 'America/Sao_Paulo',
        'status' => 'confirmed',
        'source' => 'dashboard',
    ]);

    $response = $this->actingAs($user)->put(route('bookings.update', $booking), [
        'service_id' => $service->id,
        'starts_on' => now('America/Sao_Paulo')->toDateString(),
        'starts_time' => now('America/Sao_Paulo')->addMinutes(95)->format('H:i'),
    ]);

    $response->assertSessionHasErrors('booking');
});

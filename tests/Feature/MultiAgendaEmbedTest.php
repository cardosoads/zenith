<?php

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

function makeActiveProviderWithAgenda(): array
{
    Role::findOrCreate('provider');

    $plan = Plan::query()->firstOrCreate([
        'slug' => 'starter-multi-agenda-test',
    ], [
        'name' => 'Starter Multi Agenda',
        'price_cents' => 4900,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ]);

    $user = User::factory()->create();
    $user->assignRole('provider');

    $profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'provider-multi-'.str()->lower(str()->random(8)),
        'display_name' => 'Provider Multi',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Active,
        'billing_status' => 'active',
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

    $agenda = ProviderAgenda::query()->create([
        'provider_profile_id' => $profile->id,
        'name' => 'Agenda Base',
        'slug' => 'agenda-base',
        'timezone' => 'America/Sao_Paulo',
        'is_published' => false,
        'theme' => 'auto',
        'accent' => 'sky',
        'density' => 'medium',
        'preset' => 'clean',
        'embed_height' => 680,
        'customer_extra_fields' => [],
    ]);

    return [$user->refresh(), $profile, $agenda];
}

test('provider can manage multiple agendas', function () {
    [$user] = makeActiveProviderWithAgenda();

    $response = $this->actingAs($user)->post(route('provider.agendas.store'), [
        'name' => 'Agenda Consultoria',
        'slug' => 'agenda-consultoria',
        'description' => 'Agenda de consultoria',
        'timezone' => 'America/Sao_Paulo',
    ]);

    $response->assertRedirect(route('provider.agendas.index'));

    $this->assertDatabaseHas('provider_agendas', [
        'provider_profile_id' => $user->providerProfile->id,
        'slug' => 'agenda-consultoria',
    ]);
});

test('draft agenda is not publicly accessible', function () {
    [, $profile, $agenda] = makeActiveProviderWithAgenda();

    $response = $this->get(route('widget.show', [
        'providerProfile' => $profile->slug,
        'agenda' => $agenda->slug,
    ]));

    $response->assertNotFound();
});

test('published agenda is publicly accessible', function () {
    [, $profile, $agenda] = makeActiveProviderWithAgenda();

    $agenda->update(['is_published' => true]);

    $response = $this->get(route('widget.show', [
        'providerProfile' => $profile->slug,
        'agenda' => $agenda->slug,
    ]));

    $response->assertOk();
});

test('services are scoped by agenda', function () {
    [$user, $profile, $agendaOne] = makeActiveProviderWithAgenda();

    $agendaTwo = ProviderAgenda::query()->create([
        'provider_profile_id' => $profile->id,
        'name' => 'Agenda Dois',
        'slug' => 'agenda-dois',
        'timezone' => 'America/Sao_Paulo',
        'is_published' => false,
        'theme' => 'auto',
        'accent' => 'sky',
        'density' => 'medium',
        'preset' => 'clean',
        'embed_height' => 680,
        'customer_extra_fields' => [],
    ]);

    Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agendaOne->id,
        'name' => 'Servico Agenda 1',
        'duration_minutes' => 60,
        'break_minutes' => 10,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agendaTwo->id,
        'name' => 'Servico Agenda 2',
        'duration_minutes' => 60,
        'break_minutes' => 10,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('services.index', ['agenda_id' => $agendaOne->id]));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Provider/Services/Index')
        ->where('selectedAgendaId', $agendaOne->id)
        ->has('services', 1)
    );
});

test('bookings are scoped by agenda', function () {
    [$user, $profile, $agendaOne] = makeActiveProviderWithAgenda();

    $agendaTwo = ProviderAgenda::query()->create([
        'provider_profile_id' => $profile->id,
        'name' => 'Agenda Dois',
        'slug' => 'agenda-dois-book',
        'timezone' => 'America/Sao_Paulo',
        'is_published' => false,
        'theme' => 'auto',
        'accent' => 'sky',
        'density' => 'medium',
        'preset' => 'clean',
        'embed_height' => 680,
        'customer_extra_fields' => [],
    ]);

    $serviceOne = Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agendaOne->id,
        'name' => 'Servico A1',
        'duration_minutes' => 30,
        'break_minutes' => 0,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $serviceTwo = Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agendaTwo->id,
        'name' => 'Servico A2',
        'duration_minutes' => 30,
        'break_minutes' => 0,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    $profile->bookings()->create([
        'provider_agenda_id' => $agendaOne->id,
        'service_id' => $serviceOne->id,
        'customer_name' => 'Cliente A1',
        'customer_email' => 'a1@example.com',
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDay()->addMinutes(30),
        'timezone' => 'America/Sao_Paulo',
        'status' => 'confirmed',
        'source' => 'dashboard',
    ]);

    $profile->bookings()->create([
        'provider_agenda_id' => $agendaTwo->id,
        'service_id' => $serviceTwo->id,
        'customer_name' => 'Cliente A2',
        'customer_email' => 'a2@example.com',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addMinutes(30),
        'timezone' => 'America/Sao_Paulo',
        'status' => 'confirmed',
        'source' => 'dashboard',
    ]);

    $response = $this->actingAs($user)->get(route('bookings.index', ['agenda_id' => $agendaOne->id]));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Provider/Bookings/Index')
        ->where('selectedAgendaId', $agendaOne->id)
        ->has('bookings', 1)
    );
});

test('embed script sanitizes theme accent density and preset', function () {
    $response = $this->get(route('embed.script'));

    $response->assertOk();
    $response->assertSee("const ALLOWED_THEME = ['light', 'dark', 'auto'];", false);
    $response->assertSee("const ALLOWED_ACCENT = ['sky', 'honey', 'green'];", false);
    $response->assertSee("const ALLOWED_DENSITY = ['comfortable', 'medium'];", false);
    $response->assertSee("const ALLOWED_PRESET = ['clean', 'contrast', 'soft', 'editorial'];", false);
});

test('embed studio exposes url and snippet per agenda', function () {
    [$user, , $agenda] = makeActiveProviderWithAgenda();

    $response = $this->actingAs($user)->get(route('provider.agendas.embed', ['agenda' => $agenda->id]));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Provider/Agendas/EmbedStudio')
        ->where('agenda.id', $agenda->id)
        ->has('widgetUrl')
        ->has('embedScriptUrl')
    );
});

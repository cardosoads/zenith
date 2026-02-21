<?php

use App\Models\AvailabilityRule;
use App\Models\ProviderAgenda;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\ProviderStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('widget can create booking with pix payment payload', function () {
    $user = User::factory()->create();

    $profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'widget-provider',
        'display_name' => 'Widget Provider',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Active,
        'billing_status' => 'active',
    ]);

    $agenda = ProviderAgenda::query()->create([
        'provider_profile_id' => $profile->id,
        'name' => 'Agenda Principal',
        'slug' => 'agenda-principal',
        'timezone' => 'America/Sao_Paulo',
        'is_published' => true,
        'theme' => 'auto',
        'accent' => 'sky',
        'density' => 'medium',
        'preset' => 'clean',
        'embed_height' => 680,
        'customer_extra_fields' => [],
    ]);

    $service = Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agenda->id,
        'name' => 'Consulta',
        'duration_minutes' => 60,
        'break_minutes' => 15,
        'price_cents' => 15000,
        'is_active' => true,
    ]);

    AvailabilityRule::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agenda->id,
        'weekday' => now()->dayOfWeek,
        'starts_at' => '08:00',
        'ends_at' => '20:00',
        'is_active' => true,
    ]);

    $response = $this->postJson(route('widget.bookings.confirm', [
        'providerProfile' => $profile->slug,
        'agenda' => $agenda->slug,
    ]), [
        'service_id' => $service->id,
        'starts_at' => now()->addDay()->setTime(10, 0)->toIso8601String(),
        'customer_name' => 'Cliente Teste',
        'customer_email' => 'cliente@example.com',
        'customer_phone' => '11999999999',
        'extra_fields' => [
            [
                'field_key' => 'instagram',
                'field_label' => 'Instagram',
                'field_value' => '@cliente',
            ],
        ],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('payment.method', 'pix');
    $response->assertJsonPath('payment.status', 'awaiting_payment');
});

test('widget booking stores customer notes and attachments', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $profile = ProviderProfile::query()->create([
        'user_id' => $user->id,
        'slug' => 'widget-provider-notes',
        'display_name' => 'Widget Provider Notes',
        'timezone' => 'America/Sao_Paulo',
        'status' => ProviderStatus::Active,
        'billing_status' => 'active',
    ]);

    $agenda = ProviderAgenda::query()->create([
        'provider_profile_id' => $profile->id,
        'name' => 'Agenda Notes',
        'slug' => 'agenda-notes',
        'timezone' => 'America/Sao_Paulo',
        'is_published' => true,
        'theme' => 'auto',
        'accent' => 'sky',
        'density' => 'medium',
        'preset' => 'clean',
        'embed_height' => 680,
        'customer_extra_fields' => [],
    ]);

    $service = Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agenda->id,
        'name' => 'Consulta Gratuita',
        'duration_minutes' => 60,
        'break_minutes' => 0,
        'price_cents' => 0,
        'is_active' => true,
    ]);

    AvailabilityRule::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agenda->id,
        'weekday' => now()->dayOfWeek,
        'starts_at' => '08:00',
        'ends_at' => '20:00',
        'is_active' => true,
    ]);

    $file = UploadedFile::fake()->create('exame.pdf', 200, 'application/pdf');

    $response = $this->post(route('widget.bookings.confirm', [
        'providerProfile' => $profile->slug,
        'agenda' => $agenda->slug,
    ]), [
        'service_id' => $service->id,
        'starts_at' => now()->addDay()->setTime(11, 0)->toIso8601String(),
        'customer_name' => 'Cliente Notes',
        'customer_email' => 'notes@example.com',
        'customer_phone' => '11988887777',
        'customer_notes' => 'Cliente informou alergias e preferências.',
        'attachments' => [$file],
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('bookings', [
        'provider_agenda_id' => $agenda->id,
        'service_id' => $service->id,
        'customer_name' => 'Cliente Notes',
        'customer_notes' => 'Cliente informou alergias e preferências.',
    ]);

    $attachmentPath = \App\Models\BookingAttachment::query()->value('path');

    $this->assertDatabaseCount('booking_attachments', 1);
    Storage::disk('public')->assertExists($attachmentPath);
});

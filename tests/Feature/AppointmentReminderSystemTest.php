<?php

use App\Jobs\SendAppointmentReminders;
use App\Models\AppointmentReminder;
use App\Models\Booking;
use App\Models\ProviderAgenda;
use App\Models\ProviderProfile;
use App\Models\Service;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\postJson;

beforeEach(function () {
    config()->set('services.wuzapi.base_url', 'http://localhost:8080');
    config()->set('services.wuzapi.user_token', 'wuzapi-token');
});

test('job sends 24h appointment reminders and tracks sent metadata', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-01 10:00:00'));

    Http::fake([
        'http://localhost:8080/session/status' => Http::response([
            'data' => [
                'Connected' => true,
                'LoggedIn' => true,
            ],
        ]),
        'http://localhost:8080/chat/send/text' => Http::response(['status' => 'ok']),
    ]);

    $booking = createBookingForReminderWindow(now()->addHours(24), 'confirmed');

    app(SendAppointmentReminders::class)->handle();

    $reminder = AppointmentReminder::query()
        ->where('booking_id', $booking->id)
        ->where('type', '24h')
        ->first();

    expect($reminder)
        ->not->toBeNull()
        ->and($reminder->phone)->toBe('5511999998888')
        ->and($reminder->status)->toBe('sent')
        ->and($reminder->wuzapi_message_id)->toBe('ZENITH-'.$booking->id.'-24h')
        ->and($reminder->sent_at)->not->toBeNull();

    Http::assertSent(function (Request $request) use ($booking) {
        return $request->url() === 'http://localhost:8080/chat/send/text'
            && $request['Phone'] === '5511999998888'
            && $request['Id'] === 'ZENITH-'.$booking->id.'-24h';
    });
});

test('job skips reminder dispatch when same reminder type is already sent', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-01 10:00:00'));

    Http::fake([
        'http://localhost:8080/session/status' => Http::response([
            'data' => [
                'Connected' => true,
                'LoggedIn' => true,
            ],
        ]),
        'http://localhost:8080/chat/send/text' => Http::response(['status' => 'ok']),
    ]);

    $booking = createBookingForReminderWindow(now()->addHours(24), 'confirmed');

    AppointmentReminder::query()->create([
        'booking_id' => $booking->id,
        'phone' => '5511999998888',
        'type' => '24h',
        'wuzapi_message_id' => 'ZENITH-'.$booking->id.'-24h',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    app(SendAppointmentReminders::class)->handle();

    expect(AppointmentReminder::query()->where('booking_id', $booking->id)->where('type', '24h')->count())
        ->toBe(1);

    Http::assertSentCount(1);
});

test('wuzapi webhook updates delivered and read statuses by message id', function () {
    $booking = createBookingForReminderWindow(now()->addHours(24), 'confirmed');

    $reminder = AppointmentReminder::query()->create([
        'booking_id' => $booking->id,
        'phone' => '5511999998888',
        'type' => '24h',
        'wuzapi_message_id' => 'ZENITH-'.$booking->id.'-24h',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    postJson(route('webhooks.wuzapi'), [
        'type' => 'ReadReceipt',
        'event' => [
            'Ids' => [$reminder->wuzapi_message_id],
            'IsRead' => false,
        ],
    ])->assertOk();

    $reminder->refresh();

    expect($reminder->status)->toBe('delivered')
        ->and($reminder->delivered_at)->not->toBeNull();

    postJson(route('webhooks.wuzapi'), [
        'type' => 'ReadReceipt',
        'event' => [
            'Ids' => [$reminder->wuzapi_message_id],
            'IsRead' => true,
        ],
    ])->assertOk();

    $reminder->refresh();

    expect($reminder->status)->toBe('read')
        ->and($reminder->read_at)->not->toBeNull();
});

function createBookingForReminderWindow(Carbon $startsAt, string $status): Booking
{
    $profile = ProviderProfile::factory()->create();
    $agenda = ProviderAgenda::factory()->create([
        'provider_profile_id' => $profile->id,
    ]);

    $service = Service::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agenda->id,
        'name' => 'Consulta',
        'description' => 'Consulta inicial',
        'categories' => ['geral'],
        'duration_minutes' => 60,
        'break_minutes' => 0,
        'price_cents' => 10000,
        'is_active' => true,
    ]);

    return Booking::query()->create([
        'provider_profile_id' => $profile->id,
        'provider_agenda_id' => $agenda->id,
        'service_id' => $service->id,
        'customer_name' => 'Maria Silva',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '(11) 99999-8888',
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addHour(),
        'timezone' => 'America/Sao_Paulo',
        'status' => $status,
    ]);
}

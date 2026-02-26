<?php

namespace App\Jobs;

use App\BookingStatus;
use App\Models\AppointmentReminder;
use App\Models\Booking;
use App\Services\WuzapiService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendAppointmentReminders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $wuzapi = WuzapiService::make();

        if (! $wuzapi->isReady()) {
            Log::warning('Wuzapi is not ready for appointment reminders.');

            return;
        }

        $this->sendRemindersForWindow(
            type: '24h',
            from: now()->addHours(23),
            to: now()->addHours(25),
            template: 'Olá %s! 😊 Lembrete: você tem um agendamento amanhã às %s para %s. Para cancelar ou reagendar, entre em contato.',
            serviceBeforeTime: false,
            wuzapi: $wuzapi,
        );

        $this->sendRemindersForWindow(
            type: '1h',
            from: now()->addMinutes(50),
            to: now()->addMinutes(70),
            template: 'Olá %s! ⏰ Seu agendamento para %s é em 1 hora (às %s). Te esperamos!',
            serviceBeforeTime: true,
            wuzapi: $wuzapi,
        );
    }

    private function sendRemindersForWindow(
        string $type,
        CarbonInterface $from,
        CarbonInterface $to,
        string $template,
        bool $serviceBeforeTime,
        WuzapiService $wuzapi,
    ): void {
        Booking::query()
            ->with('service')
            ->whereBetween('starts_at', [$from, $to])
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Pending->value])
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->each(function (Booking $booking) use ($type, $template, $serviceBeforeTime, $wuzapi): void {
                $phone = $this->normalizePhone($booking->customer_phone ?? '');

                if ($phone === '') {
                    return;
                }

                $reminder = AppointmentReminder::query()->firstOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'type' => $type,
                    ],
                    [
                        'phone' => $phone,
                        'status' => 'pending',
                    ],
                );

                if (in_array($reminder->status, ['sent', 'delivered', 'read'], true)) {
                    return;
                }

                $messageId = sprintf('ZENITH-%d-%s', $booking->id, $type);

                try {
                    $response = $wuzapi->sendText(
                        phone: $phone,
                        body: $this->buildMessage($booking, $template, $serviceBeforeTime),
                        messageId: $messageId,
                    );

                    $wasRejected = is_string(data_get($response, 'error')) || data_get($response, 'status') === 'error';

                    if ($wasRejected) {
                        $reminder->update([
                            'phone' => $phone,
                            'wuzapi_message_id' => $messageId,
                            'status' => 'failed',
                            'error_message' => (string) (data_get($response, 'error') ?? data_get($response, 'message') ?? 'unknown_error'),
                            'sent_at' => null,
                        ]);

                        return;
                    }

                    $reminder->update([
                        'phone' => $phone,
                        'wuzapi_message_id' => $messageId,
                        'status' => 'sent',
                        'error_message' => null,
                        'sent_at' => now(),
                    ]);
                } catch (\Throwable $exception) {
                    $reminder->update([
                        'phone' => $phone,
                        'wuzapi_message_id' => $messageId,
                        'status' => 'failed',
                        'error_message' => Str::limit($exception->getMessage(), 1000),
                    ]);
                }
            });
    }

    private function buildMessage(Booking $booking, string $template, bool $serviceBeforeTime): string
    {
        $time = $booking->starts_at?->timezone(config('app.timezone'))->format('H:i') ?? '--:--';
        $serviceName = $booking->service?->name ?? 'seu serviço';

        if ($serviceBeforeTime) {
            return sprintf($template, $booking->customer_name, $serviceName, $time);
        }

        return sprintf($template, $booking->customer_name, $time, $serviceName);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return '';
        }

        if (! str_starts_with($digits, '55')) {
            $digits = '55'.$digits;
        }

        return $digits;
    }
}

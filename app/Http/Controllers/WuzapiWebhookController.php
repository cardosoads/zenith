<?php

namespace App\Http\Controllers;

use App\Models\AppointmentReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WuzapiWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->isValidSignature($request)) {
            return response()->json(['ok' => false, 'message' => 'invalid_signature'], 401);
        }

        $type = (string) $request->input('type', '');
        $event = $request->input('event', []);

        if ($type === 'ReadReceipt') {
            $this->handleReadReceipt($event);
        }

        if ($type === 'Message') {
            Log::info('Wuzapi incoming message received.', [
                'token' => $request->input('token'),
                'event' => $event,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    private function handleReadReceipt(array $event): void
    {
        $messageIds = collect([
            $event['Id'] ?? null,
            $event['MessageId'] ?? null,
            ...($event['Ids'] ?? []),
            ...($event['MessageIds'] ?? []),
        ])->filter(fn (mixed $id): bool => is_string($id) && $id !== '')->values()->all();

        if ($messageIds === []) {
            return;
        }

        $isRead = (bool) ($event['IsRead'] ?? false);

        if ($isRead) {
            AppointmentReminder::query()
                ->whereIn('wuzapi_message_id', $messageIds)
                ->update([
                    'status' => 'read',
                    'read_at' => now(),
                ]);

            return;
        }

        AppointmentReminder::query()
            ->whereIn('wuzapi_message_id', $messageIds)
            ->where('status', '!=', 'read')
            ->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
    }

    private function isValidSignature(Request $request): bool
    {
        $secret = (string) config('services.wuzapi.hmac_secret');

        if ($secret === '') {
            return true;
        }

        $signature = (string) $request->header('x-hmac-signature', '');

        if ($signature === '') {
            return true;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        $normalizedSignature = str_starts_with($signature, 'sha256=')
            ? substr($signature, 7)
            : $signature;

        return hash_equals($expected, $normalizedSignature);
    }
}

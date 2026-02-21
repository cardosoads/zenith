<?php

namespace App\Http\Controllers;

use App\BookingStatus;
use App\Contracts\PixProviderInterface;
use App\Models\BookingPayment;
use App\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PixWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, PixProviderInterface $pixProvider): JsonResponse
    {
        $data = $pixProvider->parseWebhookPayload($request->all());

        $payment = BookingPayment::query()
            ->where('provider', $provider)
            ->where('external_id', $data['external_id'])
            ->firstOrFail();

        if ($data['status'] === 'paid') {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ]);

            $payment->booking()->update([
                'status' => BookingStatus::Confirmed,
            ]);
        }

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Contracts\BillingProviderInterface;
use App\Models\ProviderSubscription;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingWebhookController extends Controller
{
    public function __invoke(Request $request, string $provider, BillingProviderInterface $billingProvider): JsonResponse
    {
        $data = $billingProvider->parseWebhookPayload($request->all());

        $subscription = ProviderSubscription::query()
            ->where('payment_provider', $provider)
            ->where('external_id', $data['external_id'])
            ->firstOrFail();

        if ($data['status'] === 'paid') {
            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            $subscription->providerProfile()->update([
                'status' => ProviderStatus::Active,
                'billing_status' => 'active',
            ]);
        }

        return response()->json(['ok' => true]);
    }
}

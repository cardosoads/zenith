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
        $data = $billingProvider->parseWebhookPayload(
            $request->getContent(),
            ['Stripe-Signature' => $request->header('Stripe-Signature', '')],
        );

        if (! $data['external_id']) {
            return response()->json(['ok' => true]);
        }

        $subscription = ProviderSubscription::query()
            ->where('payment_provider', $provider)
            ->where('external_id', $data['external_id'])
            ->first();

        if (! $subscription) {
            return response()->json(['ok' => true]);
        }

        match ($data['status']) {
            'active' => $this->activateSubscription($subscription),
            'past_due' => $subscription->update(['status' => SubscriptionStatus::PastDue]),
            'cancelled' => $this->cancelSubscription($subscription),
            default => null,
        };

        return response()->json(['ok' => true]);
    }

    private function activateSubscription(ProviderSubscription $subscription): void
    {
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

    private function cancelSubscription(ProviderSubscription $subscription): void
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
        ]);

        $subscription->providerProfile()->update([
            'billing_status' => 'cancelled',
        ]);
    }
}

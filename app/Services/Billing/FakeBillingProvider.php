<?php

namespace App\Services\Billing;

use App\Contracts\BillingProviderInterface;
use App\Models\Plan;
use App\Models\ProviderProfile;
use Illuminate\Support\Str;

class FakeBillingProvider implements BillingProviderInterface
{
    public function createCheckout(ProviderProfile $providerProfile, Plan $plan): array
    {
        $externalId = 'bill_'.Str::uuid()->toString();

        return [
            'checkout_url' => route('onboarding.show').'?checkout='.$externalId,
            'external_id' => $externalId,
            'status' => 'pending',
        ];
    }

    public function parseWebhookPayload(array $payload): array
    {
        return [
            'external_id' => (string) ($payload['external_id'] ?? ''),
            'status' => (string) ($payload['status'] ?? 'pending'),
        ];
    }
}

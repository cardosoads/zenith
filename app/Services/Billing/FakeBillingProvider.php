<?php

namespace App\Services\Billing;

use App\Contracts\BillingProviderInterface;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Support\Str;

class FakeBillingProvider implements BillingProviderInterface
{
    public function createSubscription(ProviderProfile $providerProfile, Plan $plan, User $user): array
    {
        $externalId = 'sub_fake_'.Str::uuid()->toString();

        return [
            'client_secret' => 'pi_fake_secret_'.Str::random(24),
            'external_id' => $externalId,
            'status' => 'incomplete',
            'customer_id' => 'cus_fake_'.Str::random(14),
        ];
    }

    public function parseWebhookPayload(string $payload, array $headers = []): array
    {
        $data = json_decode($payload, true) ?? [];

        return [
            'event_type' => (string) ($data['event_type'] ?? 'invoice.payment_succeeded'),
            'external_id' => (string) ($data['external_id'] ?? ''),
            'status' => (string) ($data['status'] ?? 'active'),
        ];
    }
}

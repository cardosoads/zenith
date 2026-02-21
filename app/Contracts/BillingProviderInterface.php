<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\ProviderProfile;

interface BillingProviderInterface
{
    /**
     * @return array{checkout_url:string,external_id:string,status:string}
     */
    public function createCheckout(ProviderProfile $providerProfile, Plan $plan): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array{external_id:string,status:string}
     */
    public function parseWebhookPayload(array $payload): array;
}

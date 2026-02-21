<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\User;

interface BillingProviderInterface
{
    /**
     * @return array{client_secret:string|null,external_id:string,status:string,customer_id:string|null}
     */
    public function createSubscription(ProviderProfile $providerProfile, Plan $plan, User $user): array;

    /**
     * @param  string  $payload  Raw request body
     * @param  array<string, string>  $headers
     * @return array{event_type:string,external_id:string,status:string}
     */
    public function parseWebhookPayload(string $payload, array $headers = []): array;
}

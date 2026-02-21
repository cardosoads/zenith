<?php

namespace App\Services\Billing;

use App\Contracts\BillingProviderInterface;
use App\Models\PaymentSetting;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\User;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeBillingProvider implements BillingProviderInterface
{
    public function createSubscription(ProviderProfile $providerProfile, Plan $plan, User $user): array
    {
        $stripe = $this->client();

        $customerId = $providerProfile->stripe_customer_id;

        if (! $customerId) {
            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'provider_profile_id' => $providerProfile->id,
                    'user_id' => $user->id,
                ],
            ]);

            $customerId = $customer->id;
            $providerProfile->update(['stripe_customer_id' => $customerId]);
        }

        $subscription = $stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => [
                ['price' => $plan->stripe_price_id],
            ],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        $clientSecret = $subscription->latest_invoice->payment_intent?->client_secret;

        return [
            'client_secret' => $clientSecret,
            'external_id' => $subscription->id,
            'status' => $subscription->status,
            'customer_id' => $customerId,
        ];
    }

    public function parseWebhookPayload(string $payload, array $headers = []): array
    {
        $settings = PaymentSetting::instance();
        $sigHeader = $headers['Stripe-Signature'] ?? $headers['stripe-signature'] ?? '';

        $event = Webhook::constructEvent($payload, $sigHeader, $settings->stripe_webhook_secret);

        $eventType = $event->type;
        $object = $event->data->object;

        $externalId = match ($eventType) {
            'invoice.payment_succeeded', 'invoice.payment_failed' => $object->subscription ?? '',
            'customer.subscription.updated', 'customer.subscription.deleted' => $object->id ?? '',
            default => '',
        };

        $status = match ($eventType) {
            'invoice.payment_succeeded' => 'active',
            'invoice.payment_failed' => 'past_due',
            'customer.subscription.deleted' => 'cancelled',
            'customer.subscription.updated' => $object->status ?? 'active',
            default => 'unknown',
        };

        return [
            'event_type' => $eventType,
            'external_id' => (string) $externalId,
            'status' => $status,
        ];
    }

    private function client(): StripeClient
    {
        $settings = PaymentSetting::instance();

        return new StripeClient($settings->stripe_secret_key);
    }
}

<?php

namespace App\Http\Controllers;

use App\Contracts\BillingProviderInterface;
use App\Models\PaymentSetting;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(Request $request): Response
    {
        $profile = $request->user()->providerProfile;
        $settings = PaymentSetting::instance();

        return Inertia::render('Onboarding/Show', [
            'profile' => $profile,
            'plans' => Plan::query()->where('is_active', true)->get(),
            'subscription' => $profile?->currentSubscription,
            'stripePublicKey' => $settings->hasStripeKeys() ? $settings->stripe_public_key : null,
        ]);
    }

    public function checkout(Request $request, BillingProviderInterface $billingProvider): JsonResponse
    {
        $payload = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $user = $request->user();
        $plan = Plan::query()->findOrFail($payload['plan_id']);

        $profile = $user->providerProfile;
        if (! $profile) {
            $profile = ProviderProfile::query()->create([
                'user_id' => $user->id,
                'slug' => Str::slug($user->name.'-'.Str::random(6)),
                'display_name' => $user->name,
                'timezone' => 'America/Sao_Paulo',
                'status' => ProviderStatus::Pending,
                'billing_status' => 'pending',
            ]);
        }

        $result = $billingProvider->createSubscription($profile, $plan, $user);

        ProviderSubscription::query()->create([
            'provider_profile_id' => $profile->id,
            'plan_id' => $plan->id,
            'payment_provider' => 'stripe',
            'external_id' => $result['external_id'],
            'status' => SubscriptionStatus::Pending,
        ]);

        return response()->json([
            'client_secret' => $result['client_secret'],
            'subscription_id' => $result['external_id'],
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        $subscription = $profile->currentSubscription;
        abort_unless($subscription, 404);

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $profile->update([
            'status' => ProviderStatus::Active,
            'billing_status' => 'active',
        ]);

        $profile->agendas()->firstOrCreate(
            ['slug' => 'agenda-principal'],
            [
                'name' => 'Agenda Principal',
                'description' => 'Agenda inicial para atendimento',
                'timezone' => $profile->timezone,
                'is_published' => false,
                'theme' => 'auto',
                'accent' => 'sky',
                'density' => 'medium',
                'preset' => 'clean',
                'embed_height' => 680,
                'customer_extra_fields' => [],
            ]
        );

        return redirect()->route('dashboard')->with('status', 'subscription-active');
    }
}

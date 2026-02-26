<?php

namespace App\Http\Controllers;

use App\Contracts\BillingProviderInterface;
use App\Http\Requests\StoreCheckoutRequest;
use App\Models\PaymentSetting;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(): Response
    {
        $settings = PaymentSetting::instance();

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->get()
            ->map(function (Plan $plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price_cents' => $plan->price_cents,
                    'description' => $plan->description,
                    'popular' => $plan->slug === 'profissional',
                    'features' => $this->featuresForPlan($plan->slug),
                ];
            });

        return Inertia::render('Checkout', [
            'plans' => $plans,
            'stripePublicKey' => $settings->hasStripeKeys() ? $settings->stripe_public_key : null,
        ]);
    }

    public function store(StoreCheckoutRequest $request, BillingProviderInterface $billingProvider): JsonResponse
    {
        $validated = $request->validated();
        $plan = Plan::query()->findOrFail($validated['plan_id']);

        return DB::transaction(function () use ($validated, $plan, $billingProvider) {
            $user = User::query()->create([
                'name' => $validated['first_name'].' '.$validated['last_name'],
                'email' => $validated['email'],
                'cpf' => $validated['document'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('provider');

            $profile = ProviderProfile::query()->create([
                'user_id' => $user->id,
                'slug' => Str::slug($validated['business_name'] ?: $user->name).'-'.Str::random(6),
                'display_name' => $validated['business_name'] ?: $user->name,
                'timezone' => 'America/Sao_Paulo',
                'status' => ProviderStatus::Pending,
                'billing_status' => 'pending',
            ]);

            $result = $billingProvider->createSubscription($profile, $plan, $user);

            ProviderSubscription::query()->create([
                'provider_profile_id' => $profile->id,
                'plan_id' => $plan->id,
                'payment_provider' => 'stripe',
                'external_id' => $result['external_id'],
                'status' => SubscriptionStatus::Pending,
            ]);

            Auth::login($user);

            return response()->json([
                'client_secret' => $result['client_secret'],
                'subscription_id' => $result['external_id'],
            ]);
        });
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

    private function featuresForPlan(string $slug): array
    {
        return match ($slug) {
            'starter' => [
                '1 profissional',
                '1 agenda',
                'Até 100 agendamentos/mês',
                'Link de agendamento',
                'Gestão de clientes',
                'Suporte por email',
            ],
            'profissional' => [
                'Até 5 profissionais',
                'Agendas ilimitadas',
                'Agendamentos ilimitados',
                'Pagamentos integrados',
                'Relatórios avançados',
                'Suporte prioritário',
                'Personalização de marca',
            ],
            'empresa' => [
                'Profissionais ilimitados',
                'Agendas ilimitadas',
                'Agendamentos ilimitados',
                'Pagamentos integrados',
                'Relatórios completos',
                'Suporte 24/7',
                'API de integração',
                'Gerente de conta dedicado',
            ],
            default => [],
        };
    }
}

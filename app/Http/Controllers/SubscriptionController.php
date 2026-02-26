<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\SubscriptionStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()?->providerProfile;
        $subscription = $profile?->currentSubscription;
        $plan = $subscription?->plan;

        $isTrialing = $subscription?->status === SubscriptionStatus::Trialing;
        $trialEndsAt = $profile?->trial_ends_at;
        $bookingsQuery = $profile?->bookings();

        return Inertia::render('Provider/Subscription/Index', [
            'subscription' => [
                'current_plan_id' => $plan?->id,
                'plan_name' => $plan?->name ?? 'Nenhum',
                'status' => $subscription?->status?->value ?? 'none',
                'price' => $plan ? $plan->price_cents / 100 : 0,
                'renewal_date' => $subscription?->current_period_end?->format('Y-m-d'),
                'is_trialing' => $isTrialing,
                'trial_ends_at' => $trialEndsAt?->format('Y-m-d'),
                'trial_days_remaining' => $isTrialing && $trialEndsAt ? (int) now()->diffInDays($trialEndsAt, false) : 0,
                'usage' => [
                    'professionals' => [
                        'current' => 1,
                        'limit' => $plan?->slug === 'starter' ? 1 : ($plan?->slug === 'growth' ? 5 : null),
                    ],
                    'appointments' => [
                        'current' => (int) ($bookingsQuery?->count() ?? 0),
                        'limit' => null,
                    ],
                    'customers' => [
                        'current' => (int) ($bookingsQuery?->clone()->distinct('customer_email')->count('customer_email') ?? 0),
                        'limit' => null,
                    ],
                ],
                'payment_method' => [
                    'type' => $subscription?->payment_provider,
                    'last4' => $isTrialing ? 'TRIAL' : '----',
                    'expiry' => $subscription?->current_period_end?->format('m/Y') ?? '--/----',
                ],
                'invoices' => $profile
                    ? $profile->subscriptions()
                        ->with('plan')
                        ->latest('created_at')
                        ->get()
                        ->map(fn ($history) => [
                            'id' => $history->external_id ?? sprintf('SUB-%06d', $history->id),
                            'date' => $history->created_at?->format('Y-m-d'),
                            'plan' => $history->plan?->name,
                            'amount' => $history->plan ? $history->plan->price_cents / 100 : 0,
                            'status' => $history->status->value,
                        ])
                        ->values()
                    : [],
            ],
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price_cents')
                ->get()
                ->map(fn (Plan $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'price_cents' => $p->price_cents,
                ]),

        ]);
    }
}

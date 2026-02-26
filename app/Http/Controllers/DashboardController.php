<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Plan;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\Service;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use App\Support\AgendaContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AgendaContext $agendaContext): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return Inertia::render('Admin/Dashboard', [
                'metrics' => $this->buildAdminMetrics(),
            ]);
        }

        $profile = $user->providerProfile;

        if (! $profile) {
            $profile = ProviderProfile::query()->create([
                'user_id' => $user->id,
                'slug' => Str::slug($user->name . '-' . Str::random(6)),
                'display_name' => $user->name,
                'timezone' => 'America/Sao_Paulo',
                'status' => ProviderStatus::Pending,
                'billing_status' => 'pending',
            ]);
        }

        $subscription = $profile->currentSubscription;

        $isAllowedSubscription = $subscription
            && (
                $subscription->status === SubscriptionStatus::Active
                || ($subscription->status === SubscriptionStatus::Trialing && ! $profile->trialExpired())
            );

        if (! $isAllowedSubscription || $profile->status !== ProviderStatus::Active) {
            return redirect()->route('onboarding.show');
        }

        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        $serviceMetrics = $selectedAgenda
            ? Service::query()
                ->where('provider_agenda_id', $selectedAgenda->id)
                ->withCount('bookings')
                ->withCount([
                    'bookings as paid_bookings_count' => fn($query) => $query->whereHas('payment', fn($payment) => $payment->where('status', 'paid')),
                ])
                ->orderByDesc('bookings_count')
                ->get()
                ->map(fn($service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price_cents' => (int) $service->price_cents,
                    'bookings_count' => (int) $service->bookings_count,
                    'projected_revenue_cents' => (int) $service->bookings_count * (int) $service->price_cents,
                    'paid_revenue_cents' => (int) $service->paid_bookings_count * (int) $service->price_cents,
                ])
                ->values()
            : [];

        return Inertia::render('Provider/Dashboard', [
            'profile' => $profile,
            'agendas' => $profile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published']),
            'selectedAgendaId' => $selectedAgenda?->id,
            'metrics' => [
                'services' => $selectedAgenda ? $selectedAgenda->services()->count() : 0,
                'bookingsTotal' => $selectedAgenda ? $selectedAgenda->bookings()->count() : 0,
                'bookingsPaid' => $selectedAgenda
                    ? $selectedAgenda->bookings()->whereHas('payment', fn($query) => $query->where('status', 'paid'))->count()
                    : 0,
                'bookingsPending' => $selectedAgenda
                    ? $selectedAgenda->bookings()->where('status', 'pending')->count()
                    : 0,
            ],
            'serviceMetrics' => $serviceMetrics,
            'recentBookings' => $selectedAgenda
                ? $selectedAgenda->bookings()->latest()->limit(8)->with('service')->get()
                : [],
            'recentServices' => $selectedAgenda
                ? $selectedAgenda->services()->latest()->limit(5)->get()
                : [],
        ]);
    }

    private function buildAdminMetrics(): array
    {
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // ── Active subscriptions ──────────────────────────────────────────
        $activeSubscriptions = ProviderSubscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->with('plan')
            ->get();

        $totalActiveSubscribers = $activeSubscriptions->count();

        // ── MRR ──────────────────────────────────────────────────────────
        $mrr = $activeSubscriptions->sum(fn($sub) => (int) $sub->plan?->price_cents);

        // ── Previous month active subs (for growth & churn) ───────────────
        $prevMonthActiveCount = ProviderSubscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->where('created_at', '<=', $prevMonthEnd)
            ->count();

        // MoM growth %
        $growth = $prevMonthActiveCount > 0
            ? round((($totalActiveSubscribers - $prevMonthActiveCount) / $prevMonthActiveCount) * 100, 1)
            : ($totalActiveSubscribers > 0 ? 100 : 0);

        // Churned: subscriptions cancelled/expired between last month start and end
        $churned = ProviderSubscription::query()
            ->whereIn('status', ['cancelled', 'expired'])
            ->whereBetween('updated_at', [$prevMonthStart, $prevMonthEnd])
            ->count();

        $churnRate = $prevMonthActiveCount > 0
            ? round(($churned / $prevMonthActiveCount) * 100, 1)
            : 0;

        // ── ARPU ─────────────────────────────────────────────────────────
        $arpu = $totalActiveSubscribers > 0 ? intdiv($mrr, $totalActiveSubscribers) : 0;

        // ── LTV = ARPU / churn_rate (if churn > 0) ───────────────────────
        $ltv = ($churnRate > 0) ? (int) round($arpu / ($churnRate / 100)) : $arpu * 24;

        // ── Revenue by plan ───────────────────────────────────────────────
        $revenueByPlan = Plan::query()
            ->withCount(['subscriptions as active_count' => fn($q) => $q->where('status', SubscriptionStatus::Active->value)])
            ->get()
            ->map(fn($plan) => [
                'name' => $plan->name,
                'active_count' => (int) $plan->active_count,
                'revenue_cents' => (int) $plan->active_count * (int) $plan->price_cents,
                'price_cents' => (int) $plan->price_cents,
            ])
            ->values();

        // ── 6-month monthly series ─────────────────────────────────────────
        $months = collect(range(5, 0))->map(fn($i) => $now->copy()->subMonths($i));

        $monthlySeries = $months->map(function ($month) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $newSubs = ProviderSubscription::query()
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $cancelled = ProviderSubscription::query()
                ->whereIn('status', ['cancelled', 'expired'])
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $activeSubs = ProviderSubscription::query()
                ->where('status', SubscriptionStatus::Active->value)
                ->where('created_at', '<=', $end)
                ->with('plan')
                ->get();

            $mrrSnap = $activeSubs->sum(fn($s) => (int) $s->plan?->price_cents);

            $bookingsCount = Booking::query()
                ->whereBetween('created_at', [$start, $end])
                ->count();

            return [
                'label' => $month->translatedFormat('M/y'),
                'mrr_cents' => $mrrSnap,
                'new_subscribers' => $newSubs,
                'cancelled_subscribers' => $cancelled,
                'bookings' => $bookingsCount,
            ];
        })->values();

        // ── Platform usage ─────────────────────────────────────────────────
        $totalBookings = Booking::query()->count();
        $bookingsThisMonth = Booking::query()->where('created_at', '>=', $currentMonthStart)->count();
        $activeProviders = ProviderProfile::query()->where('status', ProviderStatus::Active->value)->count();
        $totalProviders = ProviderProfile::query()->count();
        $totalUsers = User::query()->count();

        // ── Revenue by segment ─────────────────────────────────────────────
        $revenueBySegment = ProviderProfile::query()
            ->whereNotNull('segment')
            ->where('status', ProviderStatus::Active->value)
            ->with(['currentSubscription.plan'])
            ->get()
            ->groupBy('segment')
            ->map(function ($profiles, $segment) {
                $revCents = $profiles->sum(fn($p) => (int) $p->currentSubscription?->plan?->price_cents);
                return [
                    'segment' => $segment,
                    'count' => $profiles->count(),
                    'revenue_cents' => $revCents,
                ];
            })
            ->sortByDesc('revenue_cents')
            ->values();

        return [
            'mrr_cents' => $mrr,
            'arpu_cents' => $arpu,
            'ltv_cents' => $ltv,
            'total_active_subscribers' => $totalActiveSubscribers,
            'mom_growth_pct' => $growth,
            'churn_rate_pct' => $churnRate,
            'total_providers' => $totalProviders,
            'active_providers' => $activeProviders,
            'total_users' => $totalUsers,
            'total_bookings' => $totalBookings,
            'bookings_this_month' => $bookingsThisMonth,
            'revenue_by_plan' => $revenueByPlan,
            'revenue_by_segment' => $revenueBySegment,
            'monthly_series' => $monthlySeries,
        ];
    }
}

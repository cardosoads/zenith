<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use App\ProviderStatus;
use App\SubscriptionStatus;
use App\Support\AgendaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'metrics' => [
                    'providers' => ProviderProfile::query()->count(),
                    'activeProviders' => ProviderProfile::query()->where('status', ProviderStatus::Active->value)->count(),
                    'bookings' => Booking::query()->count(),
                    'users' => User::query()->count(),
                ],
            ]);
        }

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

        $subscription = $profile->currentSubscription;

        if (! $subscription || $subscription->status !== SubscriptionStatus::Active || $profile->status !== ProviderStatus::Active) {
            return redirect()->route('onboarding.show');
        }

        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        $serviceMetrics = $selectedAgenda
            ? Service::query()
                ->where('provider_agenda_id', $selectedAgenda->id)
                ->withCount('bookings')
                ->withCount([
                    'bookings as paid_bookings_count' => fn ($query) => $query->whereHas('payment', fn ($payment) => $payment->where('status', 'paid')),
                ])
                ->orderByDesc('bookings_count')
                ->get()
                ->map(fn ($service) => [
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
                    ? $selectedAgenda->bookings()->whereHas('payment', fn ($query) => $query->where('status', 'paid'))->count()
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
}

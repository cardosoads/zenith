<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\AgendaContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request, AgendaContext $agendaContext): Response
    {
        $profile = $request->user()->providerProfile;
        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $start30 = $now->copy()->subDays(30);
        $start90 = $now->copy()->subDays(90);

        // Base query scoped to profile + optional agenda
        $baseQuery = Booking::query()
            ->where('provider_profile_id', $profile->id);

        if ($selectedAgenda) {
            $baseQuery->where('provider_agenda_id', $selectedAgenda->id);
        }

        // --- KPIs (last 30 days) ---
        $bookings30d = (clone $baseQuery)->where('starts_at', '>=', $start30)->with('service')->get();

        $totalRevenue = $bookings30d->where('status', 'completed')
            ->sum(fn($b) => $b->service?->price_cents ?? 0);

        $totalAppointments = $bookings30d->count();

        // Unique new customers (simplified: distinct customer names in period)
        $newCustomers = $bookings30d->unique('customer_name')->count();

        $cancelledCount = $bookings30d->where('status', 'cancelled')->count();
        $cancellationRate = $totalAppointments > 0
            ? round(($cancelledCount / $totalAppointments) * 100, 1)
            : 0;

        // --- Revenue by month (last 12 months) ---
        $revenueByMonth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $revenue = (clone $baseQuery)
                ->where('status', 'completed')
                ->whereBetween('starts_at', [$monthStart, $monthEnd])
                ->with('service')
                ->get()
                ->sum(fn($b) => $b->service?->price_cents ?? 0);

            $revenueByMonth[] = [
                'name' => ucfirst($month->translatedFormat('M')),
                'value' => round($revenue / 100, 2),
            ];
        }

        // --- Appointments per day of week (last 30 days) ---
        $dayNames = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        $appointmentsByDay = array_fill(0, 7, 0);

        foreach ($bookings30d as $booking) {
            $dow = Carbon::parse($booking->starts_at)->dayOfWeek; // 0=Sun
            $appointmentsByDay[$dow]++;
        }

        $appointmentsPerDay = [];
        // Start from Monday (1)
        foreach ([1, 2, 3, 4, 5, 6, 0] as $dow) {
            $appointmentsPerDay[] = [
                'name' => $dayNames[$dow],
                'value' => $appointmentsByDay[$dow],
            ];
        }

        // --- Top services ---
        $allBookings90 = (clone $baseQuery)
            ->where('starts_at', '>=', $start90)
            ->with('service')
            ->get();

        $serviceStats = $allBookings90->groupBy(fn($b) => $b->service?->name ?? 'Sem nome')
            ->map(fn($group, $name) => [
                'name' => $name,
                'quantity' => $group->count(),
                'revenue' => round($group->sum(fn($b) => $b->service?->price_cents ?? 0) / 100, 2),
            ])
            ->sortByDesc('quantity')
            ->values()
            ->take(5)
            ->toArray();

        // --- Service breakdown (pie chart) ---
        $totalServiceCount = max(array_sum(array_column($serviceStats, 'quantity')), 1);
        $chartColors = ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#6b7280'];
        $serviceBreakdown = collect($serviceStats)->map(function ($s, $i) use ($totalServiceCount, $chartColors) {
            return [
                'name' => $s['name'],
                'value' => round(($s['quantity'] / $totalServiceCount) * 100),
                'color' => $chartColors[$i] ?? '#6b7280',
            ];
        })->toArray();

        return Inertia::render('Provider/Reports/Index', [
            'kpis' => [
                'revenue' => round($totalRevenue / 100, 2),
                'appointments' => $totalAppointments,
                'newCustomers' => $newCustomers,
                'cancellationRate' => $cancellationRate,
            ],
            'revenueByMonth' => $revenueByMonth,
            'appointmentsPerDay' => $appointmentsPerDay,
            'serviceBreakdown' => $serviceBreakdown,
            'topServices' => $serviceStats,
            'agendas' => $profile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published']),
            'selectedAgendaId' => $selectedAgenda?->id,
        ]);
    }
}

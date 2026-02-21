<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\AgendaContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request, AgendaContext $agendaContext): Response
    {
        $profile = $request->user()->providerProfile;
        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        $query = Booking::query()
            ->where('provider_profile_id', $profile->id);

        if ($selectedAgenda) {
            $query->where('provider_agenda_id', $selectedAgenda->id);
        }

        $customers = $query->select('customer_name', 'customer_email', 'customer_phone')
            ->selectRaw('count(*) as bookings_count')
            ->selectRaw('max(starts_at) as last_booking_at')
            ->groupBy('customer_name', 'customer_email', 'customer_phone')
            ->orderBy('last_booking_at', 'desc')
            ->get();

        return Inertia::render('Provider/Customers/Index', [
            'customers' => $customers,
            'agendas' => $profile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published']),
            'selectedAgendaId' => $selectedAgenda?->id,
        ]);
    }
}

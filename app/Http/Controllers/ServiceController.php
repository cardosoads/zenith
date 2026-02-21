<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Support\AgendaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request, AgendaContext $agendaContext): Response
    {
        $profile = $request->user()->providerProfile;
        $selectedAgenda = $agendaContext->resolveForProvider($request, $profile);

        return Inertia::render('Provider/Services/Index', [
            'services' => $selectedAgenda ? $selectedAgenda->services()->latest()->get() : [],
            'agendas' => $profile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published']),
            'selectedAgendaId' => $selectedAgenda?->id,
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        $payload = $request->validated();

        $profile->services()->create($payload);

        return redirect()->route('services.index', ['agenda_id' => $payload['provider_agenda_id']])->with('status', 'service-created');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        abort_unless($service->provider_profile_id === $request->user()->providerProfile->id, 404);

        $payload = $request->validated();
        $service->update($payload);

        return redirect()->route('services.index', ['agenda_id' => $service->provider_agenda_id])->with('status', 'service-updated');
    }

    public function destroy(Request $request, Service $service): RedirectResponse
    {
        abort_unless($service->provider_profile_id === $request->user()->providerProfile->id, 404);

        $agendaId = $service->provider_agenda_id;
        $service->delete();

        return redirect()->route('services.index', ['agenda_id' => $agendaId])->with('status', 'service-deleted');
    }
}

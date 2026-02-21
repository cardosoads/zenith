<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProviderAgendaRequest;
use App\Http\Requests\UpdateProviderAgendaRequest;
use App\Models\ProviderAgenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProviderAgendaController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->providerProfile;

        return Inertia::render('Provider/Agendas/Index', [
            'agendas' => $profile->agendas()
                ->with(['services', 'availabilityRules'])
                ->orderBy('created_at')
                ->get(),
            'providerProfile' => $profile,
        ]);
    }

    public function store(StoreProviderAgendaRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        $payload = $request->validated();
        $slug = Str::slug($payload['slug']);

        $exists = $profile->agendas()->where('slug', $slug)->exists();
        abort_if($exists, 422, 'Slug de agenda já está em uso.');

        $profile->agendas()->create([
            'name' => $payload['name'],
            'slug' => $slug,
            'description' => $payload['description'] ?? null,
            'timezone' => $payload['timezone'] ?? $profile->timezone,
            'is_published' => false,
            'theme' => 'auto',
            'accent' => 'sky',
            'density' => 'medium',
            'preset' => 'clean',
            'embed_height' => 680,
            'customer_extra_fields' => [],
        ]);

        return redirect()->route('provider.agendas.index')->with('status', 'agenda-created');
    }

    public function update(UpdateProviderAgendaRequest $request, ProviderAgenda $agenda): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($agenda->provider_profile_id === $profile->id, 404);

        $payload = $request->validated();
        $slug = Str::slug($payload['slug']);

        $exists = $profile->agendas()
            ->where('slug', $slug)
            ->where('id', '!=', $agenda->id)
            ->exists();

        abort_if($exists, 422, 'Slug de agenda já está em uso.');

        $agenda->update([
            'name' => $payload['name'],
            'slug' => $slug,
            'description' => $payload['description'] ?? null,
            'timezone' => $payload['timezone'] ?? $profile->timezone,
            'is_published' => $payload['is_published'] ?? $agenda->is_published,
        ]);

        return redirect()->route('provider.agendas.index')->with('status', 'agenda-updated');
    }

    public function destroy(Request $request, ProviderAgenda $agenda): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($agenda->provider_profile_id === $profile->id, 404);

        $agenda->delete();

        return redirect()->route('provider.agendas.index')->with('status', 'agenda-deleted');
    }

    public function publish(Request $request, ProviderAgenda $agenda): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($agenda->provider_profile_id === $profile->id, 404);

        $agenda->update([
            'is_published' => !$agenda->is_published,
        ]);

        return redirect()->route('provider.agendas.embed', ['agenda' => $agenda->id])->with('status', 'agenda-publish-updated');
    }
}

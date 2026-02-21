<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmbedSettingsRequest;
use App\Models\ProviderAgenda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderEmbedStudioController extends Controller
{
    public function show(Request $request, ProviderAgenda $agenda): Response
    {
        $profile = $request->user()->providerProfile;
        abort_unless($agenda->provider_profile_id === $profile->id, 404);

        return Inertia::render('Provider/Agendas/EmbedStudio', [
            'agenda' => $agenda,
            'provider' => $profile->only(['slug', 'display_name']),
            'embedScriptUrl' => route('embed.script'),
            'widgetUrl' => route('widget.show', [
                'providerProfile' => $profile->slug,
                'agenda' => $agenda->slug,
            ]),
        ]);
    }

    public function update(UpdateEmbedSettingsRequest $request, ProviderAgenda $agenda): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($agenda->provider_profile_id === $profile->id, 404);

        $agenda->update($request->validated());

        return redirect()->route('provider.agendas.embed', ['agenda' => $agenda->id])->with('status', 'embed-settings-updated');
    }
}

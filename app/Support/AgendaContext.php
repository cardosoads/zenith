<?php

namespace App\Support;

use App\Models\ProviderAgenda;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;

class AgendaContext
{
    public function resolveForProvider(Request $request, ProviderProfile $providerProfile): ?ProviderAgenda
    {
        $selectedAgendaId = $request->integer('agenda_id');

        if ($selectedAgendaId > 0) {
            return $providerProfile->agendas()->whereKey($selectedAgendaId)->first();
        }

        return $providerProfile->agendas()->orderBy('name')->first();
    }

    public function resolveFromRoute(ProviderProfile $providerProfile, ProviderAgenda $agenda): ProviderAgenda
    {
        abort_unless($agenda->provider_profile_id === $providerProfile->id, 404);

        return $agenda;
    }
}

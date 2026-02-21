<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $providerProfile = $request->user()?->providerProfile;
        $hasProviderAgendasTable = Schema::hasTable('provider_agendas');
        $fallbackAgendaId = ($providerProfile && $hasProviderAgendasTable)
            ? $providerProfile->agendas()->orderBy('name')->value('id')
            : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()?->getRoleNames() ?? [],
            ],
            'providerContext' => [
                'agendas' => $providerProfile
                    ? ($hasProviderAgendasTable
                        ? $providerProfile->agendas()->orderBy('name')->get(['id', 'name', 'slug', 'is_published'])
                        : [])
                    : [],
                'selectedAgendaId' => $request->integer('agenda_id') ?: $fallbackAgendaId,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            'ui' => [
                'defaultTheme' => 'auto',
                'defaultDensity' => 'medium',
            ],
        ];
    }
}

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

        $agenda = $profile->agendas()->create([
            'name' => $payload['name'],
            'slug' => $slug,
            'description' => $payload['description'] ?? null,
            'timezone' => $payload['timezone'] ?? $profile->timezone,
            'is_published' => true, // Auto publish on create for better DX
            'theme' => 'auto',
            'accent' => 'sky',
            'density' => 'medium',
            'preset' => 'clean',
            'primary_color' => $payload['primaryColor'] ?? '#18181b',
            'secondary_color' => $payload['secondaryColor'] ?? '#27272a',
            'payment_requirement' => $payload['payment_requirement'] ?? 'none',
            'embed_height' => 680,
            'embed_width' => $payload['embed_width'] ?? 480,
            'transparent_bg' => $payload['transparent_bg'] ?? false,
            'customer_extra_fields' => [],
        ]);

        // Save Services
        $services = $request->input('services', []);
        $interval = $request->input('interval', 30);
        foreach ($services as $serviceData) {
            $price = $serviceData['isFree'] ? '0' : ($serviceData['price'] ?: '0');
            $agenda->services()->create([
                'provider_profile_id' => $profile->id,
                'name' => $serviceData['name'],
                'price_cents' => (int) (str_replace(',', '.', (string) $price) * 100),
                'duration_minutes' => $interval,
                'is_active' => true,
            ]);
        }

        // Save Availability Rules
        $weekdays = $request->input('weekdays', []);
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');

        $weekdayMap = ['seg' => 1, 'ter' => 2, 'qua' => 3, 'qui' => 4, 'sex' => 5, 'sab' => 6, 'dom' => 0];

        foreach ($weekdays as $day) {
            if (isset($weekdayMap[$day])) {
                $agenda->availabilityRules()->create([
                    'provider_profile_id' => $profile->id,
                    'weekday' => $weekdayMap[$day],
                    'starts_at' => $startTime,
                    'ends_at' => $endTime,
                    'is_active' => true,
                ]);
            }
        }

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
            'primary_color' => $payload['primaryColor'] ?? $agenda->primary_color,
            'secondary_color' => $payload['secondaryColor'] ?? $agenda->secondary_color,
            'payment_requirement' => $payload['payment_requirement'] ?? $agenda->payment_requirement,
            'is_published' => $payload['is_published'] ?? $agenda->is_published,
            'embed_width' => $payload['embed_width'] ?? $agenda->embed_width,
            'transparent_bg' => $payload['transparent_bg'] ?? $agenda->transparent_bg,
        ]);

        // Sync Services (Surgical update to avoid FK violations)
        $servicesData = $request->input('services', []);
        $interval = $request->input('interval', 30);
        $incomingServiceIds = [];

        foreach ($servicesData as $serviceData) {
            $price = $serviceData['isFree'] ? '0' : ($serviceData['price'] ?: '0');
            $updateData = [
                'provider_profile_id' => $profile->id,
                'name' => $serviceData['name'],
                'price_cents' => (int) (str_replace(',', '.', (string) $price) * 100),
                'duration_minutes' => $interval,
                'is_active' => true,
            ];

            // If it's an existing service (id is a small number, not a temporary timestamp)
            if (isset($serviceData['id']) && $serviceData['id'] < 2000000000) {
                $service = $agenda->services()->find($serviceData['id']);
                if ($service) {
                    $service->update($updateData);
                    $incomingServiceIds[] = $service->id;
                } else {
                    $newService = $agenda->services()->create($updateData);
                    $incomingServiceIds[] = $newService->id;
                }
            } else {
                $newService = $agenda->services()->create($updateData);
                $incomingServiceIds[] = $newService->id;
            }
        }

        // Deactivate or delete services not in the request
        $agenda->services()->whereNotIn('id', $incomingServiceIds)->get()->each(function ($service) {
            try {
                $service->delete();
            } catch (\Exception $e) {
                // If has bookings, just deactivate
                $service->update(['is_active' => false]);
            }
        });

        // Sync Availability Rules (These usually don't have FKs pointing to them, so delete/recreate is relatively safe)
        $agenda->availabilityRules()->delete();
        $weekdays = $request->input('weekdays', []);
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');

        $weekdayMap = ['seg' => 1, 'ter' => 2, 'qua' => 3, 'qui' => 4, 'sex' => 5, 'sab' => 6, 'dom' => 0];

        foreach ($weekdays as $day) {
            if (isset($weekdayMap[$day])) {
                $agenda->availabilityRules()->create([
                    'provider_profile_id' => $profile->id,
                    'weekday' => $weekdayMap[$day],
                    'starts_at' => $startTime,
                    'ends_at' => $endTime,
                    'is_active' => true,
                ]);
            }
        }

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

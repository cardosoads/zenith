<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateBusinessSettingsRequest;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Http\Requests\UploadProfileLogoRequest;
use App\Models\NotificationPreference;
use App\Models\TeamMember;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->providerProfile;

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'business' => [
                'display_name' => $profile?->display_name ?? '',
                'cnpj' => $profile?->cnpj ?? '',
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'address' => $profile?->address ?? '',
                'timezone' => $profile?->timezone ?? 'America/Sao_Paulo',
                'currency' => $profile?->currency ?? 'BRL',
                'logo_url' => $profile?->logo_path ? Storage::url($profile->logo_path) : null,
                'slug' => $profile?->slug,
                'public_url' => $profile ? url("/w/{$profile->slug}") : null,
            ],
            'team' => $profile
                ? $profile->teamMembers()
                    ->orderBy('name')
                    ->get()
                    ->map(fn (TeamMember $member) => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'role' => $member->role,
                        'is_active' => $member->is_active,
                        'avatar' => collect(explode(' ', $member->name))
                            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
                            ->take(2)
                            ->join(''),
                    ])
                : collect(),
            'notifications' => $profile?->notificationPreference ?? new NotificationPreference([
                'email_new_booking' => true,
                'email_cancellation' => true,
                'email_reminders' => true,
                'whatsapp_confirmation' => true,
                'whatsapp_reminder_24h' => true,
                'whatsapp_cancellation' => true,
            ]),
            'integrations' => $this->getIntegrations(),
            'sessions' => $this->getSessions($request),
            'appearance' => [
                'public_url' => $profile?->slug ? url("/w/{$profile->slug}") : null,
                'agendas' => $profile
                    ? $profile->agendas()->select('id', 'name', 'slug', 'theme', 'accent', 'is_published')->get()
                    : collect(),
            ],
        ]);
    }

    public function updateBusiness(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);

        $profile->update([
            'display_name' => $validated['display_name'],
            'cnpj' => $validated['cnpj'] ?? null,
            'address' => $validated['address'] ?? null,
            'timezone' => $validated['timezone'],
            'currency' => $validated['currency'],
        ]);

        $request->user()->update([
            'phone' => $validated['phone'] ?? null,
        ]);

        return back();
    }

    public function uploadLogo(UploadProfileLogoRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);

        if ($profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
        }

        $path = $request->file('logo')->store('logos', 'public');
        $profile->update(['logo_path' => $path]);

        return back();
    }

    public function deleteLogo(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);

        if ($profile->logo_path) {
            Storage::disk('public')->delete($profile->logo_path);
            $profile->update(['logo_path' => null]);
        }

        return back();
    }

    public function updateNotifications(UpdateNotificationPreferencesRequest $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($profile, 404);

        $profile->notificationPreference()->updateOrCreate(
            ['provider_profile_id' => $profile->id],
            $request->validated()
        );

        return back();
    }

    public function destroySession(Request $request, string $session): RedirectResponse
    {
        DB::table('sessions')
            ->where('id', $session)
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->delete();

        return back();
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function getIntegrations(): array
    {
        return [
            [
                'type' => 'whatsapp',
                'name' => 'WhatsApp Business',
                'description' => 'Envie lembretes e mensagens automáticas.',
                'status' => $this->getWhatsAppStatus(),
                'connected_at' => null,
            ],
            [
                'type' => 'google_calendar',
                'name' => 'Google Calendar',
                'description' => 'Sincronize agendamentos automaticamente.',
                'status' => 'coming_soon',
                'connected_at' => null,
            ],
            [
                'type' => 'instagram',
                'name' => 'Instagram',
                'description' => 'Agendamentos direto pelo seu perfil.',
                'status' => 'coming_soon',
                'connected_at' => null,
            ],
        ];
    }

    private function getWhatsAppStatus(): string
    {
        try {
            $wuzapi = \App\Services\WuzapiService::make();

            return $wuzapi->isReady() ? 'connected' : 'disconnected';
        } catch (\Throwable) {
            return 'disconnected';
        }
    }

    private function getSessions(Request $request): array
    {
        return collect(
            DB::table('sessions')
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->orderByDesc('last_activity')
                ->get()
        )->map(fn (object $session) => [
            'id' => $session->id,
            'ip_address' => $session->ip_address,
            'user_agent' => $session->user_agent,
            'last_activity' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            'is_current' => $session->id === $request->session()->getId(),
        ])->values()->all();
    }
}

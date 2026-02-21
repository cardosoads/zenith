<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\ProviderStatus;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProviderManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Providers/Index', [
            'providers' => ProviderProfile::query()->with(['user', 'currentSubscription.plan'])->latest()->get(),
        ]);
    }

    public function activate(ProviderProfile $providerProfile): RedirectResponse
    {
        $providerProfile->update([
            'status' => ProviderStatus::Active,
            'billing_status' => 'active',
        ]);

        return redirect()->route('admin.providers.index');
    }

    public function suspend(ProviderProfile $providerProfile): RedirectResponse
    {
        $providerProfile->update([
            'status' => ProviderStatus::Suspended,
            'billing_status' => 'suspended',
        ]);

        return redirect()->route('admin.providers.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProviderPaymentSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProviderPaymentSettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->providerProfile;

        return Inertia::render('Provider/PaymentSettings/Index', [
            'paymentSettings' => [
                'pix_key' => $profile?->pix_key,
                'pix_key_type' => $profile?->pix_key_type,
                'pix_holder_name' => $profile?->pix_holder_name,
                'pix_holder_document' => $profile?->pix_holder_document,
            ],
        ]);
    }

    public function update(UpdateProviderPaymentSettingsRequest $request): RedirectResponse
    {
        $request->user()->providerProfile->update($request->validated());

        return redirect()->route('provider.payment-settings.index')->with('status', 'payment-settings-updated');
    }
}

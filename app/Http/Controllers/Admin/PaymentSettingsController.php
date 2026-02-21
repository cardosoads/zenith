<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingsRequest;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingsController extends Controller
{
    public function index(): Response
    {
        $settings = PaymentSetting::instance();

        return Inertia::render('Admin/PaymentSettings/Index', [
            'settings' => [
                'stripe_public_key' => $settings->stripe_public_key ? $this->mask($settings->stripe_public_key) : '',
                'stripe_secret_key' => $settings->stripe_secret_key ? $this->mask($settings->stripe_secret_key) : '',
                'stripe_webhook_secret' => $settings->stripe_webhook_secret ? $this->mask($settings->stripe_webhook_secret) : '',
                'card_enabled' => $settings->card_enabled,
                'pix_enabled' => $settings->pix_enabled,
                'has_stripe_keys' => $settings->hasStripeKeys(),
            ],
        ]);
    }

    public function update(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $settings = PaymentSetting::instance();
        $data = $request->validated();

        $keysToUpdate = ['card_enabled', 'pix_enabled'];

        foreach (['stripe_public_key', 'stripe_secret_key', 'stripe_webhook_secret'] as $key) {
            if (isset($data[$key]) && $data[$key] && ! str_contains($data[$key], '••••')) {
                $keysToUpdate[] = $key;
            }
        }

        $settings->update(array_intersect_key($data, array_flip($keysToUpdate)));

        return redirect()->route('admin.payment-settings.index')->with('status', 'settings-updated');
    }

    private function mask(string $value): string
    {
        if (strlen($value) <= 8) {
            return str_repeat('•', strlen($value));
        }

        return substr($value, 0, 7).'••••'.substr($value, -4);
    }
}

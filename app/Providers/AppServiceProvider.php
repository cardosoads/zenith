<?php

namespace App\Providers;

use App\Contracts\BillingProviderInterface;
use App\Contracts\PixProviderInterface;
use App\Models\PaymentSetting;
use App\Services\Billing\FakeBillingProvider;
use App\Services\Billing\StripeBillingProvider;
use App\Services\Pix\FakePixProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BillingProviderInterface::class, function () {
            $settings = PaymentSetting::query()->first();

            if ($settings && $settings->hasStripeKeys()) {
                return new StripeBillingProvider;
            }

            return new FakeBillingProvider;
        });

        $this->app->bind(PixProviderInterface::class, FakePixProvider::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}

<?php

use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ProviderManagementController;
use App\Http\Controllers\AvailabilityRuleController;
use App\Http\Controllers\BillingWebhookController;
use App\Http\Controllers\BookingAttachmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmbedController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PixWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProviderAgendaController;
use App\Http\Controllers\ProviderEmbedStudioController;
use App\Http\Controllers\ProviderPaymentSettingsController;
use App\Http\Controllers\PublicWidgetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])->middleware(['auth'])->name('checkout.confirm');

Route::get('/embed/widget.js', [EmbedController::class, 'script'])->name('embed.script');

Route::prefix('/w/{providerProfile:slug}/{agenda:slug}')
    ->scopeBindings()
    ->group(function (): void {
        Route::get('/', [PublicWidgetController::class, 'show'])->name('widget.show');
        Route::get('/services', [PublicWidgetController::class, 'services'])->name('widget.services');
        Route::get('/availability', [PublicWidgetController::class, 'availability'])->name('widget.availability');
        Route::post('/bookings/preview', [PublicWidgetController::class, 'preview'])->name('widget.bookings.preview');
        Route::post('/bookings/confirm', [PublicWidgetController::class, 'confirm'])->name('widget.bookings.confirm');
        Route::get('/bookings/{booking}/payment', [PublicWidgetController::class, 'payment'])->name('widget.bookings.payment');
        Route::post('/bookings/search', [PublicWidgetController::class, 'search'])->name('widget.bookings.search');
        Route::post('/bookings/{booking}/cancel', [PublicWidgetController::class, 'cancel'])->name('widget.bookings.cancel');
        Route::post('/bookings/{booking}/reschedule', [PublicWidgetController::class, 'reschedule'])->name('widget.bookings.reschedule');
    });

Route::post('/webhooks/billing/{provider}', BillingWebhookController::class)->name('webhooks.billing');
Route::post('/webhooks/pix/{provider}', PixWebhookController::class)->name('webhooks.pix');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding/checkout', [OnboardingController::class, 'checkout'])->name('onboarding.checkout');
    Route::post('/onboarding/confirm', [OnboardingController::class, 'confirm'])->name('onboarding.confirm');

    Route::middleware('provider.active')->group(function () {
        Route::resource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('availability-rules', AvailabilityRuleController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('bookings', BookingController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
        Route::get('/booking-attachments/{bookingAttachment}/download', [BookingAttachmentController::class, 'download'])
            ->name('booking-attachments.download');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');

        Route::get('/provider/agendas', [ProviderAgendaController::class, 'index'])
            ->name('provider.agendas.index');
        Route::post('/provider/agendas', [ProviderAgendaController::class, 'store'])
            ->name('provider.agendas.store');
        Route::put('/provider/agendas/{agenda}', [ProviderAgendaController::class, 'update'])
            ->name('provider.agendas.update');
        Route::delete('/provider/agendas/{agenda}', [ProviderAgendaController::class, 'destroy'])
            ->name('provider.agendas.destroy');
        Route::post('/provider/agendas/{agenda}/publish', [ProviderAgendaController::class, 'publish'])
            ->name('provider.agendas.publish');

        Route::get('/provider/agendas/{agenda}/embed', [ProviderEmbedStudioController::class, 'show'])
            ->name('provider.agendas.embed');
        Route::patch('/provider/agendas/{agenda}/embed', [ProviderEmbedStudioController::class, 'update'])
            ->name('provider.agendas.embed.update');

        Route::get('/provider/payment-settings', [ProviderPaymentSettingsController::class, 'index'])
            ->name('provider.payment-settings.index');
        Route::patch('/provider/payment-settings', [ProviderPaymentSettingsController::class, 'update'])
            ->name('provider.payment-settings.update');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/providers', [ProviderManagementController::class, 'index'])->name('providers.index');
        Route::post('/providers/{providerProfile}/activate', [ProviderManagementController::class, 'activate'])->name('providers.activate');
        Route::post('/providers/{providerProfile}/suspend', [ProviderManagementController::class, 'suspend'])->name('providers.suspend');

        Route::get('/payment-settings', [PaymentSettingsController::class, 'index'])->name('payment-settings.index');
        Route::patch('/payment-settings', [PaymentSettingsController::class, 'update'])->name('payment-settings.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

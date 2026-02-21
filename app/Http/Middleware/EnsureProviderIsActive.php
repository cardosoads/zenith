<?php

namespace App\Http\Middleware;

use App\ProviderStatus;
use App\SubscriptionStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('provider')) {
            abort(403);
        }

        $profile = $user->providerProfile;

        if (! $profile || $profile->status !== ProviderStatus::Active) {
            return redirect()->route('onboarding.show');
        }

        $subscription = $profile->currentSubscription;

        if (! $subscription || $subscription->status !== SubscriptionStatus::Active) {
            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }
}

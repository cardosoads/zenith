<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\Plan;
use App\ProviderStatus;
use App\SubscriptionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProviderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'provider@zenith.test')->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Prestador Demo',
                'email' => 'provider@zenith.test',
                'password' => bcrypt('password'),
            ]);
            $user->syncRoles(['provider']);
        }

        $profile = ProviderProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'slug' => 'prestador-demo',
                'display_name' => 'Prestador Demo',
                'timezone' => 'America/Sao_Paulo',
                'status' => ProviderStatus::Active,
                'billing_status' => 'paid',
            ]
        );

        $plan = Plan::where('slug', 'growth')->first();

        if ($plan) {
            ProviderSubscription::updateOrCreate(
                ['provider_profile_id' => $profile->id],
                [
                    'plan_id' => $plan->id,
                    'status' => SubscriptionStatus::Active,
                    'payment_provider' => 'manual',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                ]
            );
        }

        // Create a default agenda for the demo provider
        $profile->agendas()->updateOrCreate(
            ['slug' => 'agenda-principal'],
            [
                'name' => 'Agenda Principal',
                'is_published' => true,
            ]
        );
    }
}

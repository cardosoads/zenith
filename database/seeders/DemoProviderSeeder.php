<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProviderProfile;
use App\Models\ProviderSubscription;
use App\Models\Plan;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ProviderAgenda;
use App\ProviderStatus;
use App\SubscriptionStatus;
use App\BookingStatus;
use App\BookingSource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProviderSeeder extends Seeder
{
    public function run(): void
    {
        $starterPlan = Plan::where('slug', 'starter')->first();
        $growthPlan = Plan::where('slug', 'growth')->first();

        if (!$starterPlan || !$growthPlan) {
            $this->command->warn('Plans not found. Skipping.');
            return;
        }

        $demoData = [
            ['name' => 'Médico Demo', 'segment' => 'Médico', 'email' => 'medico@zenith.test', 'plan' => $growthPlan, 'months' => 5],
            ['name' => 'Esteticista Demo', 'segment' => 'Esteticista', 'email' => 'esteticista@zenith.test', 'plan' => $starterPlan, 'months' => 4],
            ['name' => 'Barbearia Demo', 'segment' => 'Barbearia', 'email' => 'barbearia@zenith.test', 'plan' => $starterPlan, 'months' => 3],
            ['name' => 'Psicólogo Demo', 'segment' => 'Psicólogo', 'email' => 'psicologo@zenith.test', 'plan' => $growthPlan, 'months' => 2],
            ['name' => 'Dentista Demo', 'segment' => 'Dentista', 'email' => 'dentista@zenith.test', 'plan' => $growthPlan, 'months' => 1],
        ];

        foreach ($demoData as $i => $data) {
            // ── User ──────────────────────────────────────────────
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password')]
            );
            $user->syncRoles(['provider']);

            // ── Profile ────────────────────────────────────────────
            $profile = ProviderProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'slug' => 'demo-' . Str::slug($data['segment']) . '-' . $user->id,
                    'display_name' => $data['name'],
                    'segment' => $data['segment'],
                    'timezone' => 'America/Sao_Paulo',
                    'status' => ProviderStatus::Active,
                    'billing_status' => 'paid',
                ]
            );

            // ── Subscription ───────────────────────────────────────
            if ($profile->subscriptions()->count() === 0) {
                ProviderSubscription::create([
                    'provider_profile_id' => $profile->id,
                    'plan_id' => $data['plan']->id,
                    'status' => SubscriptionStatus::Active,
                    'payment_provider' => 'manual',
                    'current_period_start' => now()->subDays(15),
                    'current_period_end' => now()->addDays(15),
                    'created_at' => now()->subMonths($data['months']),
                    'updated_at' => now()->subMonths($data['months']),
                ]);
            }

            // ── Agenda ─────────────────────────────────────────────
            $agendaSlug = 'agenda-' . $profile->id;
            $agenda = ProviderAgenda::firstOrCreate(
                ['provider_profile_id' => $profile->id, 'slug' => $agendaSlug],
                ['name' => 'Agenda Principal', 'is_published' => true]
            );

            // ── Service ────────────────────────────────────────────
            $service = Service::firstOrCreate(
                ['provider_profile_id' => $profile->id, 'provider_agenda_id' => $agenda->id, 'name' => 'Consulta'],
                [
                    'price_cents' => ($i + 1) * 8000,
                    'duration_minutes' => 60,
                ]
            );

            // ── Bookings (only if none exist yet) ─────────────────
            if (Booking::where('provider_profile_id', $profile->id)->count() === 0) {
                for ($b = 0; $b < 5; $b++) {
                    $daysBack = rand(0, $data['months'] * 28);
                    Booking::create([
                        'provider_profile_id' => $profile->id,
                        'provider_agenda_id' => $agenda->id,
                        'service_id' => $service->id,
                        'customer_name' => 'Cliente ' . ($b + 1),
                        'customer_email' => "c{$i}{$b}@demo.test",
                        'starts_at' => now()->subDays($daysBack)->setTime(9 + $b, 0),
                        'ends_at' => now()->subDays($daysBack)->setTime(10 + $b, 0),
                        'status' => BookingStatus::Confirmed,
                        'source' => BookingSource::Widget,
                        'timezone' => 'America/Sao_Paulo',
                        'created_at' => now()->subDays($daysBack),
                    ]);
                }
            }
        }

        // ── Churned subscription ───────────────────────────────────
        $churnUser = User::firstOrCreate(
            ['email' => 'churn@zenith.test'],
            ['name' => 'Churned User', 'password' => bcrypt('password')]
        );
        $churnUser->syncRoles(['provider']);

        $churnProfile = ProviderProfile::firstOrCreate(
            ['user_id' => $churnUser->id],
            [
                'slug' => 'churn-' . $churnUser->id,
                'display_name' => 'Empresa Cancelada',
                'segment' => 'Barbearia',
                'status' => ProviderStatus::Suspended,
                'billing_status' => 'cancelled',
            ]
        );

        if ($churnProfile->subscriptions()->count() === 0) {
            ProviderSubscription::create([
                'provider_profile_id' => $churnProfile->id,
                'plan_id' => $starterPlan->id,
                'status' => SubscriptionStatus::Cancelled,
                'payment_provider' => 'manual',
                'current_period_start' => now()->subMonths(2),
                'current_period_end' => now()->subMonths(1),
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subMonth()->startOfMonth()->addDays(10),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPlansSeeder extends Seeder
{
    public function run(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('provider');

        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price_cents' => 4900,
                'billing_cycle' => 'monthly',
                'description' => 'Plano de entrada para validar operacao.',
                'is_active' => true,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'price_cents' => 9900,
                'billing_cycle' => 'monthly',
                'description' => 'Plano para operacao recorrente.',
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPlansSeeder::class);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@zenith.test'],
            ['name' => 'Zenith Admin', 'password' => 'password']
        );

        $admin->syncRoles(['admin']);

        $provider = User::query()->updateOrCreate(
            ['email' => 'provider@zenith.test'],
            ['name' => 'Prestador Demo', 'password' => 'password']
        );

        $provider->syncRoles(['provider']);
    }
}

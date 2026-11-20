<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data for local development only. Never runs in production.
 */
class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@eurograde.local'],
            ['name' => 'Admin', 'password' => 'password', 'email_verified_at' => now()],
        );
    }
}

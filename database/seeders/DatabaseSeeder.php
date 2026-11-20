<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reference/lookup data that every environment needs goes here.
        $this->call(ContentSeeder::class);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DevelopmentSeeder::class);
        }
    }
}

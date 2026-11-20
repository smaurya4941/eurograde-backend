<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SiteStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteStat>
 */
class SiteStatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'label' => fake()->words(2, true),
            'value' => fake()->numberBetween(10, 999),
            'suffix' => '+',
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }
}

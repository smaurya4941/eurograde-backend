<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\CountryStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryStat>
 */
class CountryStatFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'label' => fake()->words(2, true),
            'value' => '$'.fake()->numberBetween(500, 5000),
            'is_highlighted' => false,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function highlighted(): static
    {
        return $this->state(['is_highlighted' => true]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\CountryRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CountryRequirement>
 */
class CountryRequirementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}

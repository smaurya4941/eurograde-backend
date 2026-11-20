<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UniversityType;
use App\Models\Country;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<University>
 */
class UniversityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'University of '.fake()->unique()->city();

        return [
            'country_id' => Country::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'city' => fake()->city(),
            'logo_path' => 'universities/'.Str::slug($name).'.webp',
            'website_url' => fake()->url(),
            'type' => UniversityType::Public,
            'is_partner' => true,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function notPartner(): static
    {
        return $this->state(['is_partner' => false]);
    }
}

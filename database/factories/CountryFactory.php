<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'iso_code' => fake()->unique()->lexify('??'),
            'flag_path' => 'countries/flags/'.Str::slug($name).'.svg',
            'cover_image_path' => null,
            'excerpt' => fake()->sentence(),
            'overview' => fake()->paragraph(),
            'part_time_work' => fake()->sentence(),
            'post_study_work' => fake()->sentence(),
            'faq_category' => null,
            'meta_title' => null,
            'meta_description' => null,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TestimonialCategory;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'avatar_path' => 'testimonials/'.Str::slug($name).'.webp',
            'category' => TestimonialCategory::Admissions,
            'program' => 'Master’s Student',
            'origin' => 'India',
            'destination' => fake()->randomElement(['Germany', 'Italy']),
            'achievement' => null,
            'quote' => fake()->paragraph(),
            'rating' => 5,
            'highlights' => ['University Admission', 'Student Visa'],
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function category(TestimonialCategory $category): static
    {
        return $this->state(['category' => $category]);
    }
}

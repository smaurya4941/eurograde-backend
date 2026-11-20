<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FaqCategory;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => FaqCategory::Immigration,
            'question' => rtrim(fake()->sentence(), '.').'?',
            'answer' => fake()->paragraph(),
            'tags' => null,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
        ];
    }

    public function category(FaqCategory $category): static
    {
        return $this->state(['category' => $category]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}

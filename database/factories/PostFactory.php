<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'post_category_id' => PostCategory::factory(),
            'excerpt' => fake()->sentence(18),
            'author_id' => null,
            'body' => collect(fake()->paragraphs(4))->map(static fn (string $p) => "<p>{$p}</p>")->implode(''),
            'image_path' => 'blog/'.Str::slug($title).'.webp',
            'published_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(['published_at' => now()->addWeek()]);
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostComment>
 */
class PostCommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'parent_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->unique()->safeEmail(),
            'author_website' => null,
            'body' => fake()->paragraph(),
            'status' => CommentStatus::Approved,
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => CommentStatus::Pending, 'approved_at' => null]);
    }

    public function spam(): static
    {
        return $this->state(['status' => CommentStatus::Spam, 'approved_at' => null]);
    }

    public function replyTo(PostComment $parent): static
    {
        return $this->state(['post_id' => $parent->post_id, 'parent_id' => $parent->id]);
    }
}

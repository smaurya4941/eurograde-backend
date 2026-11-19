<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_categories_with_published_posts_alphabetically_with_counts(): void
    {
        $visa = PostCategory::factory()->create(['name' => 'Visa Rejection', 'slug' => 'visa-rejection']);
        $tips = PostCategory::factory()->create(['name' => 'Immigration Tips', 'slug' => 'immigration-tips']);
        $empty = PostCategory::factory()->create(['name' => 'Drafts Only']);

        Post::factory()->for($visa, 'category')->count(2)->create();
        Post::factory()->for($tips, 'category')->create();
        Post::factory()->for($tips, 'category')->draft()->create();
        Post::factory()->for($empty, 'category')->draft()->create();

        $this->getJson('/api/v1/post-categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'immigration-tips')
            ->assertJsonPath('data.0.posts_count', 1)
            ->assertJsonPath('data.1.slug', 'visa-rejection')
            ->assertJsonPath('data.1.posts_count', 2);
    }
}

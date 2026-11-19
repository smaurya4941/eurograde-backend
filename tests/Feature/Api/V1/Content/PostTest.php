<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_published_posts_newest_first_with_category_and_no_body(): void
    {
        $category = PostCategory::factory()->create(['name' => 'Student Visa', 'slug' => 'student-visa']);
        Post::factory()->for($category, 'category')->create(['title' => 'Older', 'published_at' => now()->subDays(3)]);
        Post::factory()->for($category, 'category')->create(['title' => 'Newest', 'published_at' => now()->subDay()]);
        Post::factory()->draft()->create();
        Post::factory()->scheduled()->create();

        $this->getJson('/api/v1/posts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Newest')
            ->assertJsonPath('data.1.title', 'Older')
            ->assertJsonPath('data.0.category.slug', 'student-visa')
            ->assertJsonPath('data.0.category.name', 'Student Visa')
            ->assertJsonMissingPath('data.0.body')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure([
                'data' => [['id', 'slug', 'title', 'category' => ['id', 'name', 'slug'], 'excerpt', 'image_url', 'published_at']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_paginates_eight_per_page_by_default(): void
    {
        Post::factory()->count(9)->create();

        $this->getJson('/api/v1/posts')
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('meta.per_page', 8)
            ->assertJsonPath('meta.last_page', 2);

        $this->getJson('/api/v1/posts?page=2')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);

        $this->getJson('/api/v1/posts?per_page=3')->assertJsonCount(3, 'data');
    }

    public function test_filters_by_category_slug(): void
    {
        $visa = PostCategory::factory()->create(['slug' => 'visa-rejection']);
        Post::factory()->for($visa, 'category')->count(2)->create();
        Post::factory()->count(3)->create();

        $this->getJson('/api/v1/posts?category=visa-rejection')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.category.slug', 'visa-rejection');

        $this->getJson('/api/v1/posts?category=unknown')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_searches_title_and_excerpt_case_insensitively(): void
    {
        Post::factory()->create(['title' => 'Permanent Residency Pathways', 'excerpt' => 'Lorem']);
        Post::factory()->create(['title' => 'Other', 'excerpt' => 'All about permanent residency.']);
        Post::factory()->create(['title' => 'Unrelated', 'excerpt' => 'Nothing here.']);

        $this->getJson('/api/v1/posts?search=PERMANENT')->assertOk()->assertJsonCount(2, 'data');
        // LIKE wildcards are treated literally.
        $this->getJson('/api/v1/posts?search=%25')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_query_parameters_are_validated(): void
    {
        $this->getJson('/api/v1/posts?per_page=100&page=0&category=bad%20slug')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page', 'page', 'category']);
    }
}

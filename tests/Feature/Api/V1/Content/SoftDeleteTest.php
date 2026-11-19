<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Enums\FaqCategory;
use App\Models\Faq;
use App\Models\Post;
use App\Models\TeamMember;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Data-integrity guarantees added by the "harden content tables" migration. */
final class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_post_disappears_but_can_be_restored(): void
    {
        $post = Post::factory()->create(['slug' => 'archived-article']);
        $this->getJson('/api/v1/posts/archived-article')->assertOk();

        $post->delete();
        $this->assertSoftDeleted($post);
        $this->getJson('/api/v1/posts/archived-article')->assertNotFound();
        $this->getJson('/api/v1/posts')->assertJsonCount(0, 'data');

        $post->restore();
        $this->getJson('/api/v1/posts/archived-article')->assertOk();
    }

    public function test_archived_author_keeps_byline_but_leaves_the_team_listing(): void
    {
        $author = TeamMember::factory()->create(['name' => 'Emily Thompson']);
        Post::factory()->create(['slug' => 'with-author', 'author_id' => $author->id]);

        $author->delete();

        $this->getJson('/api/v1/team-members')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/posts/with-author')
            ->assertOk()
            ->assertJsonPath('data.author.name', 'Emily Thompson');
    }

    public function test_post_meta_falls_back_to_title_and_excerpt(): void
    {
        Post::factory()->create(['slug' => 'plain', 'title' => 'Plain', 'excerpt' => 'Short', 'meta_title' => null]);
        Post::factory()->create(['slug' => 'seo', 'meta_title' => 'SEO title', 'meta_description' => 'SEO desc']);

        $this->getJson('/api/v1/posts/plain')->assertJsonPath('data.meta', ['title' => 'Plain', 'description' => 'Short']);
        $this->getJson('/api/v1/posts/seo')->assertJsonPath('data.meta', ['title' => 'SEO title', 'description' => 'SEO desc']);
    }

    public function test_duplicate_faq_question_in_a_category_is_rejected(): void
    {
        Faq::factory()->category(FaqCategory::Students)->create(['question' => 'Same?']);
        Faq::factory()->category(FaqCategory::Ielts)->create(['question' => 'Same?']); // other category is fine

        $this->expectException(UniqueConstraintViolationException::class);
        Faq::factory()->category(FaqCategory::Students)->create(['question' => 'Same?']);
    }
}

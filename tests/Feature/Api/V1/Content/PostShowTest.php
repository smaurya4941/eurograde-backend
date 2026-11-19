<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\Post;
use App\Models\Tag;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_a_published_post_with_body_tags_and_author(): void
    {
        $author = TeamMember::factory()->create(['name' => 'Emily Thompson', 'role' => 'Visa Documentation Specialist']);
        $post = Post::factory()->create([
            'slug' => 'ten-mistakes',
            'author_id' => $author->id,
            'body' => '<p>Intro</p><blockquote><p><strong>Quote</strong></p></blockquote><h3>1. First</h3>',
        ]);
        $post->tags()->attach([
            Tag::factory()->create(['name' => 'Visa Success', 'slug' => 'visa-success'])->id,
            Tag::factory()->create(['name' => 'Documentation', 'slug' => 'documentation'])->id,
        ]);

        $this->getJson('/api/v1/posts/ten-mistakes')
            ->assertOk()
            ->assertJsonPath('data.slug', 'ten-mistakes')
            ->assertJsonPath('data.body', '<p>Intro</p><blockquote><p><strong>Quote</strong></p></blockquote><h3>1. First</h3>')
            ->assertJsonPath('data.tags.0.name', 'Documentation') // alphabetical
            ->assertJsonPath('data.tags.1.slug', 'visa-success')
            ->assertJsonPath('data.author.name', 'Emily Thompson')
            ->assertJsonPath('data.author.role', 'Visa Documentation Specialist')
            ->assertJsonStructure([
                'data' => [
                    'id', 'slug', 'title', 'category' => ['name', 'slug'], 'excerpt', 'image_url', 'published_at', 'body',
                    'tags' => [['id', 'name', 'slug']],
                    'author' => ['name', 'role', 'bio', 'photo_url', 'socials'],
                ],
            ]);
    }

    public function test_unknown_draft_and_scheduled_posts_are_not_found(): void
    {
        Post::factory()->draft()->create(['slug' => 'a-draft']);
        Post::factory()->scheduled()->create(['slug' => 'coming-soon']);

        foreach (['does-not-exist', 'a-draft', 'coming-soon'] as $slug) {
            $this->getJson("/api/v1/posts/{$slug}")
                ->assertNotFound()
                ->assertJsonPath('success', false);
        }
    }

    public function test_publishing_a_post_makes_it_visible_despite_the_cache(): void
    {
        $post = Post::factory()->draft()->create(['slug' => 'soon']);
        $this->getJson('/api/v1/posts/soon')->assertNotFound();

        $post->update(['published_at' => now()->subMinute()]);

        $this->getJson('/api/v1/posts/soon')->assertOk();
    }

    public function test_body_html_is_sanitised_when_saved(): void
    {
        $post = Post::factory()->create([
            'body' => '<p onclick="steal()">Hi <a href="javascript:alert(1)">x</a></p><script>alert(1)</script><iframe src="https://evil.test"></iframe><a href="https://ok.test">ok</a>',
        ]);

        $body = (string) $post->fresh()?->body;

        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('javascript:', $body);
        $this->assertStringNotContainsString('<iframe', $body);
        $this->assertStringContainsString('<a href="https://ok.test" rel="noopener noreferrer">ok</a>', $body);
    }

    public function test_listing_filters_by_tag_slug(): void
    {
        $tag = Tag::factory()->create(['slug' => 'visa-approval']);
        Post::factory()->count(2)->create()->each(static fn (Post $p) => $p->tags()->attach($tag));
        Post::factory()->count(3)->create();

        $this->getJson('/api/v1/posts?tag=visa-approval')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/v1/posts?tag=not-a-tag')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/posts?tag=bad%20tag!')->assertUnprocessable();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_unauthenticated(): void
    {
        $this->getJson('/api/v1/admin/posts')->assertUnauthorized();
    }

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/posts')
            ->assertForbidden();
    }

    public function test_admin_can_list_posts_including_drafts(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->draft()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/posts')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/posts', [
                'title' => 'A New Article',
                'post_category_id' => $category->id,
                'excerpt' => 'A short summary of the article.',
                'body' => '<p>Body</p>',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'a-new-article');

        $this->assertDatabaseHas('posts', ['title' => 'A New Article']);
    }

    public function test_creating_a_post_validates_input(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/posts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'post_category_id', 'excerpt']);
    }

    public function test_admin_can_update_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/posts/{$post->id}", [
                'title' => 'Updated Title',
                'slug' => $post->slug,
                'post_category_id' => $post->post_category_id,
                'excerpt' => $post->excerpt,
            ])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_admin_can_assign_tags_to_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/posts', [
                'title' => 'Tagged Article',
                'post_category_id' => $category->id,
                'excerpt' => 'A short summary of the article.',
                'tags' => $tags->pluck('id')->all(),
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'data.tags');
    }

    public function test_admin_can_clear_tags_via_the_provided_marker(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();
        $post->tags()->attach(Tag::factory()->create());

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/posts/{$post->id}", [
                'title' => $post->title,
                'slug' => $post->slug,
                'post_category_id' => $post->post_category_id,
                'excerpt' => $post->excerpt,
                'tags_provided' => '1',
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.tags');

        $this->assertCount(0, $post->fresh()->tags);
    }

    public function test_admin_leaves_tags_untouched_without_the_provided_marker(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();
        $post->tags()->attach($tag);

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/posts/{$post->id}", [
                'title' => 'Renamed Only',
                'slug' => $post->slug,
                'post_category_id' => $post->post_category_id,
                'excerpt' => $post->excerpt,
            ])
            ->assertOk();

        $this->assertSame([$tag->id], $post->fresh()->tags->pluck('id')->all());
    }

    public function test_admin_can_delete_a_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/posts/{$post->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }
}

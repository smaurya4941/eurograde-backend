<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TagAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/tags')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_tag(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/tags', ['name' => 'Student Visa'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'student-visa');

        $this->assertDatabaseHas('tags', ['name' => 'Student Visa']);
    }

    public function test_admin_can_update_a_tag(): void
    {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/tags/{$tag->id}", ['name' => 'Renamed', 'slug' => $tag->slug])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');
    }

    public function test_admin_can_rename_a_tag_without_sending_a_slug(): void
    {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/tags/{$tag->id}", ['name' => 'Visa Rejection'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Visa Rejection')
            ->assertJsonPath('data.slug', 'visa-rejection');
    }

    public function test_admin_can_delete_a_tag(): void
    {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/tags/{$tag->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_deleting_a_tag_detaches_it_from_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $tag = Tag::factory()->create();
        $post = Post::factory()->create();
        $post->tags()->attach($tag);

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/tags/{$tag->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('post_tag', ['tag_id' => $tag->id]);
    }
}

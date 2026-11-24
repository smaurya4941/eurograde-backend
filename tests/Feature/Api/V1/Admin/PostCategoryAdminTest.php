<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostCategoryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/post-categories')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/post-categories', ['name' => 'Visa Guides'])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'visa-guides');

        $this->assertDatabaseHas('post_categories', ['name' => 'Visa Guides']);
    }

    public function test_creating_a_category_validates_a_unique_slug(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/post-categories', ['name' => 'Anything', 'slug' => $category->slug])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/post-categories/{$category->id}", ['name' => 'Renamed', 'slug' => $category->slug])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');
    }

    public function test_admin_can_rename_a_category_without_sending_a_slug(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/post-categories/{$category->id}", ['name' => 'Immigration Consultantss'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Immigration Consultantss')
            ->assertJsonPath('data.slug', 'immigration-consultantss');
    }

    public function test_admin_can_delete_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/post-categories/{$category->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('post_categories', ['id' => $category->id]);
    }

    public function test_deleting_a_category_nulls_it_on_existing_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $category = PostCategory::factory()->create();
        $post = Post::factory()->create(['post_category_id' => $category->id]);

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/post-categories/{$category->id}")
            ->assertNoContent();

        $this->assertNull($post->fresh()->post_category_id);
    }
}

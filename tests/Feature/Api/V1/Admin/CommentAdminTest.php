<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\PostComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CommentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/comments')
            ->assertForbidden();
    }

    public function test_admin_can_list_all_comments_regardless_of_status(): void
    {
        $admin = User::factory()->admin()->create();
        PostComment::factory()->pending()->create();
        PostComment::factory()->create();
        PostComment::factory()->spam()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/comments')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_filter_comments_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        PostComment::factory()->pending()->create();
        PostComment::factory()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/comments?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_admin_comment_includes_the_email_and_post_context(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = PostComment::factory()->create(['author_email' => 'reader@example.com']);

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/comments')
            ->assertOk()
            ->assertJsonPath('data.0.author_email', 'reader@example.com')
            ->assertJsonPath('data.0.post.slug', $comment->post->slug);
    }

    public function test_admin_can_approve_a_pending_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = PostComment::factory()->pending()->create();

        $this->actingAs($admin, 'web')
            ->patchJson("/api/v1/admin/comments/{$comment->id}", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertNotNull($comment->fresh()->approved_at);
    }

    public function test_admin_can_reject_an_approved_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = PostComment::factory()->create();

        $this->actingAs($admin, 'web')
            ->patchJson("/api/v1/admin/comments/{$comment->id}", ['status' => 'spam'])
            ->assertOk()
            ->assertJsonPath('data.status', 'spam');

        $this->assertNull($comment->fresh()->approved_at);
    }

    public function test_updating_a_comment_validates_the_status(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = PostComment::factory()->create();

        $this->actingAs($admin, 'web')
            ->patchJson("/api/v1/admin/comments/{$comment->id}", ['status' => 'not-a-status'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_admin_can_delete_a_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = PostComment::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/comments/{$comment->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('post_comments', ['id' => $comment->id]);
    }

    public function test_deleting_a_comment_also_removes_its_replies(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = PostComment::factory()->create();
        $reply = PostComment::factory()->replyTo($comment)->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/comments/{$comment->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('post_comments', ['id' => $reply->id]);
    }
}

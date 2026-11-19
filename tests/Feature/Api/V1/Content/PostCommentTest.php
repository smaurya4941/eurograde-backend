<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Enums\CommentStatus;
use App\Models\Post;
use App\Models\PostComment;
use App\Notifications\NewPostComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class PostCommentTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->post = Post::factory()->create(['slug' => 'ten-mistakes']);
        config(['site.contact.notify_email' => 'office@eurograde.test', 'site.comments.require_approval' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return [
            'author_name' => 'Jane Doe',
            'author_email' => 'Jane@Example.com',
            'author_website' => 'https://jane.example.com',
            'body' => 'Really helpful checklist, thank you!',
            ...$overrides,
        ];
    }

    public function test_lists_only_approved_comments_as_a_thread_without_private_fields(): void
    {
        $first = PostComment::factory()->for($this->post)->create(['author_name' => 'First', 'created_at' => now()->subDays(2)]);
        PostComment::factory()->replyTo($first)->create(['author_name' => 'Reply', 'created_at' => now()->subDay()]);
        PostComment::factory()->for($this->post)->create(['author_name' => 'Second', 'created_at' => now()->subHour()]);
        $hidden = PostComment::factory()->for($this->post)->pending()->create();
        PostComment::factory()->replyTo($hidden)->create(); // approved, but its parent is not
        PostComment::factory()->for($this->post)->spam()->create();
        PostComment::factory()->create(); // another post

        $this->getJson('/api/v1/posts/ten-mistakes/comments')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.author_name', 'First')
            ->assertJsonPath('data.0.replies.0.author_name', 'Reply')
            ->assertJsonPath('data.0.replies.0.parent_id', $first->id)
            ->assertJsonPath('data.1.author_name', 'Second')
            ->assertJsonMissingPath('data.0.author_email')
            ->assertJsonMissingPath('data.0.ip_address');
    }

    public function test_comments_on_unknown_posts_are_not_found(): void
    {
        $this->getJson('/api/v1/posts/nope/comments')->assertNotFound();
        $this->postJson('/api/v1/posts/nope/comments', $this->validPayload())->assertNotFound();
    }

    public function test_new_comment_is_held_for_moderation_and_office_is_notified(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Thanks for your comment! It will appear once it has been reviewed.')
            ->assertJsonMissingPath('data');

        $this->assertDatabaseHas('post_comments', [
            'post_id' => $this->post->id,
            'author_name' => 'Jane Doe',
            'author_email' => 'jane@example.com',
            'author_website' => 'https://jane.example.com',
            'status' => CommentStatus::Pending->value,
        ]);
        $this->getJson('/api/v1/posts/ten-mistakes/comments')->assertJsonCount(0, 'data');

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewPostComment::class,
            static fn (NewPostComment $n, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'office@eurograde.test'
                && $n->comment->author_email === 'jane@example.com',
        );
    }

    public function test_comment_is_published_immediately_when_moderation_is_off(): void
    {
        Notification::fake();
        config(['site.comments.require_approval' => false]);

        $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload(['author_website' => '']))
            ->assertCreated()
            ->assertJsonPath('message', 'Thanks for your comment! It is now live.')
            ->assertJsonPath('data.author_name', 'Jane Doe')
            ->assertJsonPath('data.author_website', null)
            ->assertJsonPath('data.status', 'approved');

        $this->getJson('/api/v1/posts/ten-mistakes/comments')->assertJsonCount(1, 'data');
    }

    public function test_replies_must_target_a_visible_comment_on_the_same_post(): void
    {
        Notification::fake();
        $parent = PostComment::factory()->for($this->post)->create();
        $pending = PostComment::factory()->for($this->post)->pending()->create();
        $elsewhere = PostComment::factory()->create();

        $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload(['parent_id' => $parent->id]))
            ->assertCreated();
        $this->assertDatabaseHas('post_comments', ['parent_id' => $parent->id, 'author_name' => 'Jane Doe']);

        foreach ([$pending->id, $elsewhere->id, 999999] as $parentId) {
            $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload(['parent_id' => $parentId]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['parent_id']);
        }
    }

    public function test_input_is_validated(): void
    {
        $this->postJson('/api/v1/posts/ten-mistakes/comments', [
            'author_name' => '',
            'author_email' => 'not-an-email',
            'author_website' => 'javascript:alert(1)',
            'body' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['author_name', 'author_email', 'author_website', 'body']);
    }

    public function test_honeypot_submissions_look_held_but_are_discarded(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload(['subject' => 'buy now']))
            ->assertCreated()
            ->assertJsonPath('message', 'Thanks for your comment! It will appear once it has been reviewed.');

        $this->assertDatabaseCount('post_comments', 0);
        Notification::assertNothingSent();
    }

    public function test_submissions_are_rate_limited(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload())->assertCreated();
        }

        $this->postJson('/api/v1/posts/ten-mistakes/comments', $this->validPayload())->assertTooManyRequests();
    }
}

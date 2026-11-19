<?php

declare(strict_types=1);

namespace App\Services\Blog;

use App\Enums\CommentStatus;
use App\Models\Post;
use App\Models\PostComment;
use App\Notifications\NewPostComment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final class PostCommentService
{
    /**
     * Approved comments as a tree (oldest first), each with its `replies` relation set.
     * Replies whose parent is not approved stay hidden along with it.
     *
     * @return array{comments: Collection<int, PostComment>, total: int}
     */
    public function threadFor(Post $post): array
    {
        $approved = $post->comments()
            ->approved()
            ->oldest()
            ->oldest('id')
            ->get();

        $byParent = $approved->groupBy(static fn (PostComment $c) => $c->parent_id ?? 0);
        $total = 0;

        $attach = static function (int $parentId) use (&$attach, $byParent, &$total): Collection {
            /** @var Collection<int, PostComment> $children */
            $children = new Collection($byParent->get($parentId)?->all() ?? []);

            foreach ($children as $comment) {
                $total++;
                $comment->setRelation('replies', $attach($comment->id));
            }

            return $children;
        };

        $roots = $attach(0);

        return ['comments' => $roots, 'total' => $total];
    }

    /**
     * Store a reader's comment. It goes live immediately only when moderation is off.
     *
     * @param  array{author_name: string, author_email: string, author_website: string|null, body: string, parent_id: int|null}  $data
     */
    public function submit(Post $post, array $data, ?string $ipAddress, ?string $userAgent): PostComment
    {
        $approved = ! config('site.comments.require_approval', true);

        $comment = $post->comments()->create([
            ...$data,
            'status' => $approved ? CommentStatus::Approved : CommentStatus::Pending,
            'approved_at' => $approved ? now() : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? Str::limit($userAgent, 500, '') : null,
        ]);

        $recipient = config('site.contact.notify_email');

        if (is_string($recipient) && $recipient !== '') {
            Notification::route('mail', $recipient)->notify(new NewPostComment($comment));
        }

        return $comment;
    }
}

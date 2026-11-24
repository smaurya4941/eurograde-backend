<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\CommentStatus;
use App\Models\PostComment;
use Illuminate\Pagination\LengthAwarePaginator;

final class CommentAdminService
{
    /**
     * @return LengthAwarePaginator<int, PostComment>
     */
    public function list(?CommentStatus $status, int $page, int $perPage): LengthAwarePaginator
    {
        return PostComment::query()
            ->with('post')
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage, page: $page);
    }

    public function updateStatus(PostComment $comment, CommentStatus $status): PostComment
    {
        $comment->update([
            'status' => $status,
            'approved_at' => $status === CommentStatus::Approved ? now() : null,
        ]);

        return $comment;
    }

    public function delete(PostComment $comment): void
    {
        $comment->delete();
    }
}

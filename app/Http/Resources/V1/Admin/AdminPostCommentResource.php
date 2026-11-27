<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Admin;

use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every field a moderator needs, including the commenter's email (hidden from the public resource).
 *
 * @mixin PostComment
 */
final class AdminPostCommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post' => [
                'id' => $this->post->id,
                'title' => $this->post->title,
                'slug' => $this->post->slug,
            ],
            'parent_id' => $this->parent_id,
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'author_website' => $this->author_website,
            'body' => $this->body,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

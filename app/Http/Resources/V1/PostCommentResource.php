<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public view of a comment. The commenter's email and IP are never exposed.
 *
 * @mixin PostComment
 */
final class PostCommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'author_name' => $this->author_name,
            'author_website' => $this->author_website,
            'body' => $this->body,
            'status' => $this->status->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'replies' => self::collection($this->whenLoaded('replies')),
        ];
    }
}

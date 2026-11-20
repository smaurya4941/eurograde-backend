<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full article: card fields plus sanitised body HTML, tags and author.
 *
 * @mixin Post
 */
final class PostDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new PostResource($this->resource))->toArray($request),
            'body' => $this->body,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'author' => new TeamMemberResource($this->whenLoaded('author')),
            'meta' => [
                'title' => $this->meta_title ?? $this->title,
                'description' => $this->meta_description ?? $this->excerpt,
            ],
        ];
    }
}

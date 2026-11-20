<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Post;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Card-sized representation of a blog post (no body).
 *
 * @mixin Post
 */
final class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'category' => new PostCategoryResource($this->whenLoaded('category')),
            'excerpt' => $this->excerpt,
            'image_url' => MediaUrl::from($this->image_path),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Admin;

use App\Http\Resources\V1\PostCategoryResource;
use App\Http\Resources\V1\TagResource;
use App\Http\Resources\V1\TeamMemberResource;
use App\Models\Post;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable field of a post, for the admin list/form.
 *
 * @mixin Post
 */
final class AdminPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'post_category_id' => $this->post_category_id,
            'category' => new PostCategoryResource($this->whenLoaded('category')),
            'author_id' => $this->author_id,
            'author' => new TeamMemberResource($this->whenLoaded('author')),
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'image_url' => MediaUrl::from($this->image_path),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

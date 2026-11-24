<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Admin;

use App\Models\Testimonial;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable field of a testimonial, for the admin list/form.
 *
 * @mixin Testimonial
 */
final class AdminTestimonialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'avatar_url' => MediaUrl::from($this->avatar_path),
            'category' => $this->category->value,
            'program' => $this->program,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'achievement' => $this->achievement,
            'quote' => $this->quote,
            'rating' => $this->rating,
            'highlights' => $this->highlights ?? [],
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

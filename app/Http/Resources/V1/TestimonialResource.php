<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Testimonial;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Testimonial
 */
final class TestimonialResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
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
        ];
    }
}

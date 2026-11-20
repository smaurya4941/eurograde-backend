<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Service;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Service
 */
final class ServiceResource extends JsonResource
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
            'excerpt' => $this->excerpt,
            'icon_url' => MediaUrl::from($this->icon_path),
            'image_url' => MediaUrl::from($this->image_path),
            'country' => $this->whenLoaded('country', fn () => $this->country === null ? null : [
                'slug' => $this->country->slug,
                'name' => $this->country->name,
            ]),
        ];
    }
}

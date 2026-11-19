<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\SuccessStory;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SuccessStory
 */
final class SuccessStoryResource extends JsonResource
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
            'destination' => $this->destination,
            'photo_url' => MediaUrl::from($this->photo_path),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\University;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin University
 */
final class UniversityResource extends JsonResource
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
            'city' => $this->city,
            'type' => $this->type->value,
            'logo_url' => MediaUrl::from($this->logo_path),
            'website_url' => $this->website_url,
            'is_partner' => $this->is_partner,
            'country' => $this->whenLoaded('country', fn () => [
                'slug' => $this->country->slug,
                'name' => $this->country->name,
            ]),
        ];
    }
}

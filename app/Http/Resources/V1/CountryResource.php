<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Country;
use App\Models\CountryStat;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Country card in the listing: identity, flag, highlighted stats and requirement count.
 *
 * @mixin Country
 */
final class CountryResource extends JsonResource
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
            'iso_code' => $this->iso_code,
            'flag_url' => MediaUrl::from($this->flag_path),
            'excerpt' => $this->excerpt,
            'highlights' => $this->whenLoaded('stats', fn () => $this->stats->map(
                static fn (CountryStat $stat) => ['label' => $stat->label, 'value' => $stat->value],
            )->values()),
            'requirements_count' => $this->whenCounted('requirements'),
        ];
    }
}

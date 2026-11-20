<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\SiteStat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public site configuration consumed by the layout (header, footer, contact blocks, counters).
 *
 * @property-read array{settings: array<string, array<string, string|null>>, stats: Collection<int, SiteStat>} $resource
 */
final class SiteSettingsResource extends JsonResource
{
    /** Social networks in display order (must match the frontend's SocialNetwork icons). */
    private const SOCIAL_NETWORKS = ['facebook', 'instagram', 'x', 'youtube'];

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contact = $this->resource['settings']['contact'] ?? [];
        $social = $this->resource['settings']['social'] ?? [];
        $phone = $contact['phone'] ?? null;
        $phoneAlt = $contact['phone_alt'] ?? null;

        $socials = [];
        foreach (self::SOCIAL_NETWORKS as $network) {
            if (! empty($social[$network])) {
                $socials[] = ['network' => $network, 'url' => $social[$network]];
            }
        }

        return [
            'contact' => [
                'phone' => $phone,
                'phone_href' => $phone === null ? null : 'tel:'.preg_replace('/[^\d+]/', '', $phone),
                'phone_alt' => $phoneAlt,
                'phone_alt_href' => $phoneAlt === null ? null : 'tel:'.preg_replace('/[^\d+]/', '', $phoneAlt),
                'email' => $contact['email'] ?? null,
                'address' => $contact['address'] ?? null,
                'hours' => $contact['hours'] ?? null,
                'hours_weekend' => $contact['hours_weekend'] ?? null,
                'map_embed_url' => $contact['map_embed_url'] ?? null,
            ],
            'socials' => $socials,
            'stats' => $this->resource['stats']->map(static fn (SiteStat $stat) => [
                'key' => $stat->key,
                'label' => $stat->label,
                'value' => $stat->value,
                'suffix' => $stat->suffix,
            ])->values(),
        ];
    }
}

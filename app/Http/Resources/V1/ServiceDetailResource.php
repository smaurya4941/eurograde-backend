<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Service;
use App\Models\ServiceFeature;
use App\Models\ServiceImage;
use App\Models\ServiceMetric;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Everything a service's detail page shows.
 *
 * @mixin Service
 */
final class ServiceDetailResource extends JsonResource
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
            'image_url' => MediaUrl::from($this->image_path),
            'banner_image_url' => MediaUrl::from($this->banner_image_path),
            'overview' => $this->overview,
            'offer' => [
                'title' => $this->offer_title,
                'highlight' => $this->offer_highlight,
                'intro' => $this->offer_intro,
                'features' => $this->features->map(static fn (ServiceFeature $f) => $f->description)->values(),
                'images' => $this->images->map(static fn (ServiceImage $i) => [
                    'url' => MediaUrl::from($i->image_path),
                    'alt' => $i->alt,
                ])->values(),
            ],
            'why' => [
                'title' => $this->why_title,
                'highlight' => $this->why_highlight,
                'intro' => $this->why_intro,
                'outro' => $this->why_outro,
                'metrics' => $this->metrics->map(static fn (ServiceMetric $m) => [
                    'label' => $m->label,
                    'value' => $m->value,
                ])->values(),
            ],
            'faq_category' => $this->faq_category?->value,
            'faq_intro' => $this->faq_intro,
            'country' => $this->country === null ? null : [
                'slug' => $this->country->slug,
                'name' => $this->country->name,
            ],
            'meta' => [
                'title' => $this->meta_title ?? $this->title,
                'description' => $this->meta_description ?? $this->excerpt,
            ],
        ];
    }
}

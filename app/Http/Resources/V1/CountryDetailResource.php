<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Country;
use App\Models\CountryCity;
use App\Models\CountryLivingCost;
use App\Models\CountryReason;
use App\Models\CountryRequirement;
use App\Models\CountryStat;
use App\Models\CountryVisa;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Everything a country's detail page shows.
 *
 * @mixin Country
 */
final class CountryDetailResource extends JsonResource
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
            'cover_image_url' => MediaUrl::from($this->cover_image_path),
            'excerpt' => $this->excerpt,
            'overview' => $this->overview,
            'why_choose' => [
                'intro' => $this->why_choose_intro,
                'image_url' => MediaUrl::from($this->why_choose_image_path),
                'reasons' => $this->reasons->map(static fn (CountryReason $r) => [
                    'title' => $r->title,
                    'description' => $r->description,
                ])->values(),
            ],
            'cities_intro' => $this->cities_intro,
            'part_time_work' => $this->part_time_work,
            'post_study_work' => $this->post_study_work,
            'faq_category' => $this->faq_category?->value,
            'faq_intro' => $this->faq_intro,
            'meta' => [
                'title' => $this->meta_title ?? $this->name,
                'description' => $this->meta_description ?? $this->excerpt,
            ],
            'stats' => $this->stats->map(static fn (CountryStat $s) => [
                'label' => $s->label,
                'value' => $s->value,
                'is_highlighted' => $s->is_highlighted,
            ])->values(),
            'requirements' => $this->requirements->map(static fn (CountryRequirement $r) => [
                'title' => $r->title,
                'description' => $r->description,
            ])->values(),
            'visas' => $this->visas->map(static fn (CountryVisa $v) => [
                'name' => $v->name,
                'description' => $v->description,
                'visa_type' => $v->visa_type,
                'fee' => $v->fee,
            ])->values(),
            'living_costs' => $this->livingCosts->map(static fn (CountryLivingCost $c) => [
                'label' => $c->label,
                'amount' => $c->amount,
            ])->values(),
            'cities' => $this->cities->map(static fn (CountryCity $c) => [
                'name' => $c->name,
                'description' => $c->description,
                'image_url' => MediaUrl::from($c->image_path),
                'universities_count' => $c->universities_count,
            ])->values(),
        ];
    }
}

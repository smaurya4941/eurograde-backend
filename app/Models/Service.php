<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FaqCategory;
use App\Models\Concerns\FlushesContentCache;
use App\Support\ArticleHtml;
use Carbon\CarbonImmutable;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Something EuroRank offers (Study in Germany, IELTS Preparation, Attestation…).
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $excerpt
 * @property string|null $overview
 * @property string|null $offer_title
 * @property string|null $offer_highlight
 * @property string|null $offer_intro
 * @property string|null $why_title
 * @property string|null $why_highlight
 * @property string|null $why_intro
 * @property string|null $why_outro
 * @property FaqCategory|null $faq_category
 * @property string|null $faq_intro
 * @property string|null $body
 * @property string|null $icon_path
 * @property string|null $image_path
 * @property string|null $banner_image_path
 * @property int|null $country_id
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Country|null $country
 * @property-read Collection<int, ServiceFeature> $features
 * @property-read Collection<int, ServiceImage> $images
 * @property-read Collection<int, ServiceMetric> $metrics
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use FlushesContentCache, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'overview',
        'offer_title',
        'offer_highlight',
        'offer_intro',
        'why_title',
        'why_highlight',
        'why_intro',
        'why_outro',
        'faq_category',
        'faq_intro',
        'body',
        'icon_path',
        'image_path',
        'banner_image_path',
        'country_id',
        'meta_title',
        'meta_description',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'faq_category' => FaqCategory::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return HasMany<ServiceFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(ServiceFeature::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ServiceImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ServiceImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<ServiceMetric, $this>
     */
    public function metrics(): HasMany
    {
        return $this->hasMany(ServiceMetric::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Rich-text body is sanitised on write, like blog articles.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value) => $value === null ? null : ArticleHtml::sanitize($value),
        );
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}

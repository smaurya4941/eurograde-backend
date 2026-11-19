<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FaqCategory;
use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Database\Factories\CountryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A study destination (Germany, Italy…) with its key facts and detail content.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $iso_code
 * @property string|null $flag_path
 * @property string|null $cover_image_path
 * @property string|null $excerpt
 * @property string|null $overview
 * @property string|null $why_choose_intro
 * @property string|null $why_choose_image_path
 * @property string|null $cities_intro
 * @property string|null $part_time_work
 * @property string|null $post_study_work
 * @property FaqCategory|null $faq_category
 * @property string|null $faq_intro
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CarbonImmutable|null $deleted_at
 * @property-read Collection<int, CountryStat> $stats
 * @property-read Collection<int, CountryRequirement> $requirements
 * @property-read Collection<int, CountryVisa> $visas
 * @property-read Collection<int, CountryLivingCost> $livingCosts
 * @property-read Collection<int, CountryCity> $cities
 * @property-read Collection<int, CountryReason> $reasons
 * @property-read int|null $requirements_count
 */
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use FlushesContentCache, HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'iso_code',
        'flag_path',
        'cover_image_path',
        'excerpt',
        'overview',
        'why_choose_intro',
        'why_choose_image_path',
        'cities_intro',
        'part_time_work',
        'post_study_work',
        'faq_category',
        'faq_intro',
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
            'faq_category' => FaqCategory::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CountryStat, $this>
     */
    public function stats(): HasMany
    {
        return $this->hasMany(CountryStat::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<CountryRequirement, $this>
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(CountryRequirement::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<CountryVisa, $this>
     */
    public function visas(): HasMany
    {
        return $this->hasMany(CountryVisa::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<CountryLivingCost, $this>
     */
    public function livingCosts(): HasMany
    {
        return $this->hasMany(CountryLivingCost::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<CountryCity, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(CountryCity::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<CountryReason, $this>
     */
    public function reasons(): HasMany
    {
        return $this->hasMany(CountryReason::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<Country>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Country>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UniversityType;
use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Database\Factories\UniversityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A (partner) university in one of our destination countries.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $slug
 * @property string|null $city
 * @property string|null $logo_path
 * @property string|null $website_url
 * @property UniversityType $type
 * @property bool $is_partner
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Country $country
 */
class University extends Model
{
    /** @use HasFactory<UniversityFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'city',
        'logo_path',
        'website_url',
        'type',
        'is_partner',
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
            'type' => UniversityType::class,
            'is_partner' => 'boolean',
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
     * Active universities whose country is also active (and not deleted).
     *
     * @param  Builder<University>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereHas('country', static fn (Builder $country) => $country->where('is_active', true));
    }

    /**
     * @param  Builder<University>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}

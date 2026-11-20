<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A popular student city in a country.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string|null $description
 * @property string|null $image_path
 * @property int|null $universities_count
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Country $country
 */
class CountryCity extends Model
{
    use FlushesContentCache;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'description',
        'image_path',
        'universities_count',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'universities_count' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Database\Factories\SiteStatFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A headline number shown by the animated counters, e.g. "counselors" → 80 + "Expert Counselors".
 *
 * @property int $id
 * @property string $key
 * @property string $label
 * @property int $value
 * @property string|null $suffix
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class SiteStat extends Model
{
    /** @use HasFactory<SiteStatFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'label', 'value', 'suffix', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<SiteStat>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<SiteStat>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Database\Factories\SuccessStoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A student portrait in the "success stories" wall: name, photo and destination.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $destination
 * @property string|null $photo_path
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class SuccessStory extends Model
{
    /** @use HasFactory<SuccessStoryFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'destination',
        'photo_path',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<SuccessStory>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<SuccessStory>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TestimonialCategory;
use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $avatar_path
 * @property TestimonialCategory $category
 * @property string $program
 * @property string|null $origin
 * @property string|null $destination
 * @property string|null $achievement
 * @property string $quote
 * @property int $rating
 * @property list<string>|null $highlights
 * @property bool $is_featured
 * @property int $sort_order
 * @property bool $is_active
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'avatar_path',
        'category',
        'program',
        'origin',
        'destination',
        'achievement',
        'quote',
        'rating',
        'highlights',
        'is_featured',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => TestimonialCategory::class,
            'rating' => 'integer',
            'highlights' => 'array',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<Testimonial>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}

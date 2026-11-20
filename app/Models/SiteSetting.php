<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Site-wide key/value setting. Keys are dotted and grouped by prefix,
 * e.g. "contact.phone", "contact.email", "social.facebook".
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property bool $is_public
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class SiteSetting extends Model
{
    use FlushesContentCache;

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value', 'is_public'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    /**
     * @param  Builder<SiteSetting>  $query
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', true);
    }
}

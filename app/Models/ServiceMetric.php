<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * "Why Us" progress bar on a service detail page (value is a percentage).
 *
 * @property int $id
 * @property int $service_id
 * @property string $label
 * @property int $value
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Service $service
 */
class ServiceMetric extends Model
{
    use FlushesContentCache;

    /**
     * @var list<string>
     */
    protected $fillable = ['service_id', 'label', 'value', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_id' => 'integer',
            'value' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

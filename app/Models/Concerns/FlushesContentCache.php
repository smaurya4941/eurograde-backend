<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\ContentCache;

/**
 * Invalidate the public content cache whenever the model changes,
 * so edits made through any admin tool are visible immediately.
 */
trait FlushesContentCache
{
    public static function bootFlushesContentCache(): void
    {
        static::saved(static fn () => ContentCache::flush());
        static::deleted(static fn () => ContentCache::flush());
    }
}

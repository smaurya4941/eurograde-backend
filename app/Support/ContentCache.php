<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Cache for public website content (team, FAQs, posts…).
 *
 * Entries are namespaced by a version number; bumping the version invalidates
 * every entry at once without needing cache tags (works with any store).
 */
final class ContentCache
{
    private const VERSION_KEY = 'content-cache:version';

    private const TTL_SECONDS = 3600;

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        return Cache::remember(self::key($key), self::TTL_SECONDS, $callback);
    }

    public static function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function key(string $key): string
    {
        return sprintf('content-cache:v%d:%s', self::version(), $key);
    }

    private static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }
}

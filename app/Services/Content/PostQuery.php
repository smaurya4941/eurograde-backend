<?php

declare(strict_types=1);

namespace App\Services\Content;

/** Filters + pagination for the public blog listing. */
final readonly class PostQuery
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 8,
        public ?string $category = null,
        public ?string $search = null,
        public ?string $tag = null,
    ) {}

    /** Free-text searches are unbounded, so they are never cached. */
    public function isCacheable(): bool
    {
        return $this->search === null;
    }

    public function cacheKey(): string
    {
        return sprintf('posts:%s:%s:%d:%d', $this->category ?? 'all', $this->tag ?? 'all', $this->perPage, $this->page);
    }
}

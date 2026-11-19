<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Enums\FaqCategory;
use App\Models\Faq;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\TeamMember;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Read-side queries for public website content. Results are cached and
 * invalidated automatically when content changes (see FlushesContentCache).
 */
final class ContentService
{
    /**
     * @return Collection<int, TeamMember>
     */
    public function teamMembers(?int $limit = null): Collection
    {
        return ContentCache::remember(
            'team-members:'.($limit ?? 'all'),
            static fn () => TeamMember::query()
                ->active()
                ->ordered()
                ->when($limit, static fn ($query, int $limit) => $query->limit($limit))
                ->get(),
        );
    }

    /**
     * @return Collection<int, Faq>
     */
    public function faqs(FaqCategory $category): Collection
    {
        return ContentCache::remember(
            'faqs:'.$category->value,
            static fn () => Faq::query()
                ->active()
                ->where('category', $category)
                ->ordered()
                ->get(),
        );
    }

    /**
     * Published posts, newest first, optionally filtered by category slug and search term.
     *
     * @return LengthAwarePaginator<int, Post>
     */
    public function posts(PostQuery $query): LengthAwarePaginator
    {
        $fetch = static function () use ($query): array {
            $builder = Post::query()
                ->published()
                ->with('category')
                ->when($query->category, static fn ($q, string $slug) => $q->inCategory($slug))
                ->when($query->tag, static fn ($q, string $slug) => $q->withTag($slug))
                ->when($query->search, static fn ($q, string $term) => $q->search($term));

            $total = (clone $builder)->count();
            $items = $builder->latestFirst()->forPage($query->page, $query->perPage)->get();

            return [$items, $total];
        };

        /** @var array{0: Collection<int, Post>, 1: int} $result */
        $result = $query->isCacheable() ? ContentCache::remember($query->cacheKey(), $fetch) : $fetch();

        return new LengthAwarePaginator($result[0], $result[1], $query->perPage, $query->page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }

    /**
     * A single published post with everything its article page shows.
     *
     * @throws ModelNotFoundException<Post> when the slug is unknown or the post is not published yet
     */
    public function post(string $slug): Post
    {
        // Look up the (small, bounded) set of published slugs first, so requests for
        // arbitrary unknown slugs are answered without creating a cache entry each.
        $published = ContentCache::remember(
            'post-slugs',
            static fn (): array => array_fill_keys(Post::query()->published()->pluck('slug')->all(), true),
        );

        if (! isset($published[$slug])) {
            throw (new ModelNotFoundException)->setModel(Post::class, [$slug]);
        }

        return ContentCache::remember(
            'post:'.$slug,
            static fn () => Post::query()
                ->published()
                ->where('slug', $slug)
                ->with(['category', 'author', 'tags'])
                ->firstOrFail(),
        );
    }

    /**
     * Categories that have at least one published post, alphabetically, with counts.
     *
     * @return Collection<int, PostCategory>
     */
    public function postCategories(): Collection
    {
        return ContentCache::remember(
            'post-categories',
            static fn () => PostCategory::query()
                ->withCount(['posts' => static fn ($q) => $q->published()])
                ->whereHas('posts', static fn ($q) => $q->published())
                ->orderBy('name')
                ->get(),
        );
    }
}

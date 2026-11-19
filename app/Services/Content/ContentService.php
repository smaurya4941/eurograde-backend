<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Enums\FaqCategory;
use App\Enums\TestimonialCategory;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\SiteStat;
use App\Models\SuccessStory;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\University;
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
     * Active testimonials in display order.
     *
     * @param  list<TestimonialCategory>  $categories  empty = every category
     * @return Collection<int, Testimonial>
     */
    public function testimonials(array $categories = [], bool $featuredOnly = false, ?int $limit = null): Collection
    {
        $values = array_map(static fn (TestimonialCategory $c) => $c->value, $categories);
        $key = sprintf(
            'testimonials:%s:%s:%s',
            $values === [] ? 'all' : implode(',', $values),
            $featuredOnly ? 'featured' : 'any',
            $limit ?? 'all',
        );

        return ContentCache::remember(
            $key,
            static fn () => Testimonial::query()
                ->active()
                ->when($values !== [], static fn ($query) => $query->whereIn('category', $values))
                ->when($featuredOnly, static fn ($query) => $query->where('is_featured', true))
                ->ordered()
                ->when($limit, static fn ($query, int $limit) => $query->limit($limit))
                ->get(),
        );
    }

    /**
     * @return Collection<int, SuccessStory>
     */
    public function successStories(?int $limit = null): Collection
    {
        return ContentCache::remember(
            'success-stories:'.($limit ?? 'all'),
            static fn () => SuccessStory::query()
                ->active()
                ->ordered()
                ->when($limit, static fn ($query, int $limit) => $query->limit($limit))
                ->get(),
        );
    }

    /**
     * Active services in display order.
     *
     * @return Collection<int, Service>
     */
    public function services(): Collection
    {
        return ContentCache::remember(
            'services',
            static fn () => Service::query()->active()->ordered()->with('country:id,slug,name')->get(),
        );
    }

    /**
     * Active universities (of active countries), optionally for one country and/or partners only.
     *
     * @return Collection<int, University>
     */
    public function universities(?string $countrySlug = null, bool $partnersOnly = false, ?int $limit = null): Collection
    {
        $key = sprintf('universities:%s:%s:%s', $countrySlug ?? 'all', $partnersOnly ? 'partners' : 'any', $limit ?? 'all');

        return ContentCache::remember(
            $key,
            static fn () => University::query()
                ->active()
                ->with('country:id,slug,name')
                ->when($countrySlug, static fn ($q, string $slug) => $q->whereHas('country', static fn ($c) => $c->where('slug', $slug)))
                ->when($partnersOnly, static fn ($q) => $q->where('is_partner', true))
                ->ordered()
                ->when($limit, static fn ($q, int $limit) => $q->limit($limit))
                ->get(),
        );
    }

    /**
     * Public site-wide settings grouped by key prefix, plus the headline stats:
     * `['contact' => [...], 'social' => [...], 'stats' => Collection<SiteStat>]`.
     *
     * @return array{settings: array<string, array<string, string|null>>, stats: Collection<int, SiteStat>}
     */
    public function siteSettings(): array
    {
        return ContentCache::remember('site-settings', static function (): array {
            $settings = [];
            foreach (SiteSetting::query()->public()->orderBy('id')->get() as $setting) {
                [$group, $name] = array_pad(explode('.', $setting->key, 2), 2, null);
                if ($name !== null) {
                    $settings[$group][$name] = $setting->value;
                }
            }

            return [
                'settings' => $settings,
                'stats' => SiteStat::query()->active()->ordered()->get(),
            ];
        });
    }

    /**
     * Active countries for the listing, with the stats highlighted on their cards.
     *
     * @return Collection<int, Country>
     */
    public function countries(): Collection
    {
        return ContentCache::remember(
            'countries',
            static fn () => Country::query()
                ->active()
                ->ordered()
                ->with(['stats' => static fn ($query) => $query->where('is_highlighted', true)])
                ->withCount('requirements')
                ->get(),
        );
    }

    /**
     * A single active country with everything its detail page shows.
     *
     * @throws ModelNotFoundException<Country> when the slug is unknown or the country is inactive
     */
    public function country(string $slug): Country
    {
        $active = ContentCache::remember(
            'country-slugs',
            static fn (): array => array_fill_keys(Country::query()->active()->pluck('slug')->all(), true),
        );

        if (! isset($active[$slug])) {
            throw (new ModelNotFoundException)->setModel(Country::class, [$slug]);
        }

        return ContentCache::remember(
            'country:'.$slug,
            static fn () => Country::query()
                ->active()
                ->where('slug', $slug)
                ->with(['stats', 'requirements', 'visas', 'livingCosts', 'cities', 'reasons'])
                ->firstOrFail(),
        );
    }

    /**
     * Active FAQs for one or more topics, grouped in the order the topics are given.
     *
     * @return Collection<int, Faq>
     */
    public function faqs(FaqCategory ...$categories): Collection
    {
        $result = new Collection;

        // Cached per topic, so pages asking for different combinations share entries.
        foreach ($categories as $category) {
            $result = $result->concat(ContentCache::remember(
                'faqs:'.$category->value,
                static fn () => Faq::query()
                    ->active()
                    ->where('category', $category)
                    ->ordered()
                    ->get(),
            ));
        }

        return $result;
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

<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesContentCache;
use App\Support\ArticleHtml;
use Carbon\CarbonImmutable;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property int|null $post_category_id
 * @property int|null $author_id
 * @property string $excerpt
 * @property string|null $body
 * @property string|null $image_path
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read PostCategory|null $category
 * @property-read TeamMember|null $author
 * @property-read Collection<int, Tag> $tags
 */
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use FlushesContentCache, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'post_category_id',
        'author_id',
        'excerpt',
        'body',
        'image_path',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<TeamMember, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'author_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    /**
     * @return HasMany<PostComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Article HTML is sanitised on write, so every reader of the column gets safe markup.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value) => $value === null ? null : ArticleHtml::sanitize($value),
        );
    }

    /**
     * Published posts only (scheduled posts stay hidden until their date).
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Post>  $query
     */
    public function scopeLatestFirst(Builder $query): void
    {
        $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * @param  Builder<Post>  $query
     */
    public function scopeInCategory(Builder $query, string $slug): void
    {
        $query->whereHas('category', static fn (Builder $category) => $category->where('slug', $slug));
    }

    /**
     * @param  Builder<Post>  $query
     */
    public function scopeWithTag(Builder $query, string $slug): void
    {
        $query->whereHas('tags', static fn (Builder $tag) => $tag->where('slug', $slug));
    }

    /**
     * Case-insensitive match on title or excerpt. LIKE wildcards in the term are escaped.
     *
     * @param  Builder<Post>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $like = '%'.addcslashes($term, '\\%_').'%';

        $query->where(static fn (Builder $q) => $q
            ->where('title', 'like', $like)
            ->orWhere('excerpt', 'like', $like));
    }
}

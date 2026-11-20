<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CommentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PostCommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A reader comment on a blog post. Public visitors submit comments, so they do
 * not flush the content cache (that would let spam thrash it); the comments
 * endpoint reads them directly instead.
 *
 * @property int $id
 * @property int $post_id
 * @property int|null $parent_id
 * @property string $author_name
 * @property string $author_email
 * @property string|null $author_website
 * @property string $body
 * @property CommentStatus $status
 * @property CarbonImmutable|null $approved_at
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Post $post
 * @property-read PostComment|null $parent
 */
class PostComment extends Model
{
    /** @use HasFactory<PostCommentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'parent_id',
        'author_name',
        'author_email',
        'author_website',
        'body',
        'status',
        'approved_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'author_email',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<PostComment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<PostComment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @param  Builder<PostComment>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', CommentStatus::Approved);
    }

    public function isApproved(): bool
    {
        return $this->status === CommentStatus::Approved;
    }
}

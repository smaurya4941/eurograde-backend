<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

/**
 * Plain CRUD for posts, bypassing the public ContentCache (admin always reads fresh;
 * writes still flush the public cache via Post's FlushesContentCache trait).
 */
final class PostAdminService
{
    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function list(int $page, int $perPage): LengthAwarePaginator
    {
        return Post::query()
            ->with(['category', 'author', 'tags'])
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>|null  $tagIds
     */
    public function create(array $data, ?UploadedFile $image, ?array $tagIds): Post
    {
        if ($image !== null) {
            $data['image_path'] = $image->store('blog', 'public');
        }

        $post = Post::create($data);

        if ($tagIds !== null) {
            $post->tags()->sync($tagIds);
        }

        return $post;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>|null  $tagIds
     */
    public function update(Post $post, array $data, ?UploadedFile $image, ?array $tagIds): Post
    {
        if ($image !== null) {
            if ($post->image_path !== null) {
                Storage::disk('public')->delete($post->image_path);
            }
            $data['image_path'] = $image->store('blog', 'public');
        }

        $post->update($data);

        if ($tagIds !== null) {
            $post->tags()->sync($tagIds);
        }

        return $post;
    }

    public function delete(Post $post): void
    {
        if ($post->image_path !== null) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();
    }
}

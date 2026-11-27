<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Collection;

final class PostCategoryAdminService
{
    /**
     * @return Collection<int, PostCategory>
     */
    public function list(): Collection
    {
        return PostCategory::query()->withCount('posts')->orderBy('name')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PostCategory
    {
        return PostCategory::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PostCategory $category, array $data): PostCategory
    {
        $category->update($data);

        return $category;
    }

    public function delete(PostCategory $category): void
    {
        $category->delete();
    }
}

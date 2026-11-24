<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

final class TagAdminService
{
    /**
     * @return Collection<int, Tag>
     */
    public function list(): Collection
    {
        return Tag::query()->withCount('posts')->orderBy('name')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Tag $tag, array $data): Tag
    {
        $tag->update($data);

        return $tag;
    }

    public function delete(Tag $tag): void
    {
        $tag->delete();
    }
}

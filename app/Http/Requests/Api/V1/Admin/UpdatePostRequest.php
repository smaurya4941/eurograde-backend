<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $post = $this->route('post');
        $postId = is_object($post) ? $post->id : $post;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($postId)],
            'post_category_id' => ['required', 'integer', 'exists:post_categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:team_members,id'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // A multipart request cannot represent an empty array, so the admin form sends this
        // marker to say "tags were edited, even if that means clearing all of them".
        if ($this->has('tags_provided') && ! $this->has('tags')) {
            $this->merge(['tags' => []]);
        }
    }

    /**
     * @return array{title: string, slug: string, post_category_id: int, author_id: int|null, excerpt: string, body: string|null, meta_title: string|null, meta_description: string|null, published_at: string|null}
     */
    public function payload(): array
    {
        /** @var array{title: string, slug: string, post_category_id: int, author_id: int|null, excerpt: string, body: string|null, meta_title: string|null, meta_description: string|null, published_at: string|null} $data */
        $data = $this->safe()->except(['image', 'tags']);

        return $data;
    }

    /**
     * @return list<int>|null
     */
    public function tagIds(): ?array
    {
        return $this->has('tags') ? $this->input('tags', []) : null;
    }
}

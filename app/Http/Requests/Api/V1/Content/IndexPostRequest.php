<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use App\Services\Content\PostQuery;
use Illuminate\Foundation\Http\FormRequest;

final class IndexPostRequest extends FormRequest
{
    public const DEFAULT_PER_PAGE = 8;

    public const MAX_PER_PAGE = 24;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
            // Unknown categories simply return no posts (no 422), like an empty archive page.
            'category' => ['sometimes', 'nullable', 'string', 'max:120', 'alpha_dash'],
            'tag' => ['sometimes', 'nullable', 'string', 'max:120', 'alpha_dash'],
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    public function toQuery(): PostQuery
    {
        $search = trim((string) $this->validated('search', ''));
        $category = (string) $this->validated('category', '');
        $tag = (string) $this->validated('tag', '');

        return new PostQuery(
            page: (int) $this->validated('page', 1),
            perPage: (int) $this->validated('per_page', self::DEFAULT_PER_PAGE),
            category: $category !== '' ? $category : null,
            search: $search !== '' ? $search : null,
            tag: $tag !== '' ? $tag : null,
        );
    }
}

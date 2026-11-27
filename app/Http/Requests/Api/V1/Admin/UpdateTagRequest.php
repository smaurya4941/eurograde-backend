<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class UpdateTagRequest extends FormRequest
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
        $tag = $this->route('tag');
        $tagId = is_object($tag) ? $tag->id : $tag;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('tags', 'slug')->ignore($tagId)],
        ];
    }

    protected function prepareForValidation(): void
    {
        // The admin's tag manager only edits the name, so re-derive the slug from it
        // whenever one isn't explicitly given (e.g. a future caller that does send one).
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
        }
    }

    /**
     * @return array{name: string, slug: string}
     */
    public function payload(): array
    {
        /** @var array{name: string, slug: string} $data */
        $data = $this->validated();

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use App\Enums\CommentStatus;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StorePostCommentRequest extends FormRequest
{
    /** Hidden field humans never fill in; bots usually do. ("website" is a real field here.) */
    public const HONEYPOT = 'subject';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $slug = (string) $this->route('slug');

        return [
            'author_name' => ['required', 'string', 'max:100'],
            'author_email' => ['required', 'string', 'email', 'max:255'],
            'author_website' => ['nullable', 'string', 'url:http,https', 'max:255'],
            'body' => ['required', 'string', 'min:2', 'max:3000'],
            // Replies may only answer a visible comment on the same article.
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('post_comments', 'id')->where(static fn (Builder $query) => $query
                    ->where('status', CommentStatus::Approved->value)
                    ->whereIn('post_id', static fn (Builder $posts) => $posts->select('id')->from('posts')->where('slug', $slug))),
            ],
            self::HONEYPOT => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'author_name' => 'name',
            'author_email' => 'email',
            'author_website' => 'website',
            'body' => 'comment',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => 'The comment you are replying to is no longer available.',
        ];
    }

    public function isSpam(): bool
    {
        return $this->filled(self::HONEYPOT);
    }

    /**
     * @return array{author_name: string, author_email: string, author_website: string|null, body: string, parent_id: int|null}
     */
    public function payload(): array
    {
        $website = $this->validated('author_website');
        $parentId = $this->validated('parent_id');

        return [
            'author_name' => (string) $this->validated('author_name'),
            'author_email' => (string) $this->validated('author_email'),
            'author_website' => is_string($website) && $website !== '' ? $website : null,
            'body' => (string) $this->validated('body'),
            'parent_id' => $parentId !== null ? (int) $parentId : null,
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'author_name' => trim((string) $this->input('author_name')),
            'author_email' => strtolower(trim((string) $this->input('author_email'))),
            'author_website' => trim((string) $this->input('author_website')) ?: null,
            // Normalise line endings and collapse runs of blank lines.
            'body' => preg_replace("/\n{3,}/", "\n\n", str_replace("\r\n", "\n", trim((string) $this->input('body')))),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use App\Enums\TestimonialCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET testimonials?category[]=german&category[]=italian&featured=1&limit=6`
 */
final class IndexTestimonialRequest extends FormRequest
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
        return [
            'category' => ['sometimes', 'array', 'max:'.count(TestimonialCategory::cases())],
            'category.*' => ['required', Rule::enum(TestimonialCategory::class)],
            'featured' => ['sometimes', 'boolean'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.LimitRequest::MAX],
        ];
    }

    /**
     * Requested categories, de-duplicated and sorted so equivalent queries share a cache entry.
     *
     * @return list<TestimonialCategory>
     */
    public function categories(): array
    {
        /** @var list<string> $values */
        $values = $this->validated('category', []);
        $values = array_values(array_unique($values));
        sort($values);

        return array_map(static fn (string $value) => TestimonialCategory::from($value), $values);
    }

    public function featuredOnly(): bool
    {
        return $this->boolean('featured');
    }

    public function limit(): ?int
    {
        $limit = $this->validated('limit');

        return $limit === null ? null : (int) $limit;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use App\Enums\FaqCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET faqs?category=students` or several topics at once:
 * `GET faqs?category[]=students&category[]=germany` (results keep the requested topic order).
 */
final class IndexFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $category = $this->input('category');

        if (is_string($category)) {
            $this->merge(['category' => [$category]]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'array', 'min:1', 'max:'.count(FaqCategory::cases())],
            'category.*' => ['required', 'distinct', Rule::enum(FaqCategory::class)],
        ];
    }

    /**
     * @return list<FaqCategory>
     */
    public function categories(): array
    {
        /** @var list<string> $values */
        $values = $this->validated('category');

        return array_map(static fn (string $value) => FaqCategory::from($value), $values);
    }
}

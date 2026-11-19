<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use App\Enums\FaqCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexFaqRequest extends FormRequest
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
            'category' => ['required', Rule::enum(FaqCategory::class)],
        ];
    }

    public function category(): FaqCategory
    {
        return FaqCategory::from((string) $this->validated('category'));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use Illuminate\Foundation\Http\FormRequest;

/** Shared `?limit=` validation for public content listings. */
final class LimitRequest extends FormRequest
{
    public const MAX = 50;

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
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.self::MAX],
        ];
    }

    public function limit(?int $default = null): ?int
    {
        $limit = $this->validated('limit');

        return $limit === null ? $default : (int) $limit;
    }
}

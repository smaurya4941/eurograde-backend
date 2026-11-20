<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Content;

use Illuminate\Foundation\Http\FormRequest;

/** `GET universities?country=germany&partners=1&limit=8` */
final class IndexUniversityRequest extends FormRequest
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
            'country' => ['sometimes', 'string', 'max:120', 'regex:/^[a-z0-9-]+$/'],
            'partners' => ['sometimes', 'boolean'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.LimitRequest::MAX],
        ];
    }

    public function country(): ?string
    {
        $country = $this->validated('country');

        return is_string($country) ? $country : null;
    }

    public function partnersOnly(): bool
    {
        return $this->boolean('partners');
    }

    public function limit(): ?int
    {
        $limit = $this->validated('limit');

        return $limit === null ? null : (int) $limit;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Forms;

use Illuminate\Foundation\Http\FormRequest;

final class StoreContactMessageRequest extends FormRequest
{
    /** Hidden field humans never fill in; bots usually do. */
    public const HONEYPOT = 'website';

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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9\s().-]{6,}$/'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'source_page' => ['nullable', 'string', 'max:100'],
            self::HONEYPOT => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }

    public function isSpam(): bool
    {
        return $this->filled(self::HONEYPOT);
    }

    /**
     * @return array{first_name: string, last_name: string, email: string, phone: string, message: string, source_page: string|null}
     */
    public function payload(): array
    {
        /** @var array{first_name: string, last_name: string, email: string, phone: string, message: string, source_page?: string|null} $data */
        $data = $this->safe()->except(self::HONEYPOT);

        return [...$data, 'source_page' => $data['source_page'] ?? null];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'phone' => trim((string) $this->input('phone')),
            'message' => trim((string) $this->input('message')),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // A multipart request cannot represent an empty array, so the admin form sends this
        // marker to say "socials were edited, even if that means clearing all of them".
        if ($this->has('socials_provided') && ! $this->has('socials')) {
            $this->merge(['socials' => []]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $teamMember = $this->route('team_member');
        $teamMemberId = is_object($teamMember) ? $teamMember->id : $teamMember;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('team_members', 'slug')->ignore($teamMemberId)],
            'role' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'socials' => ['nullable', 'array'],
            'socials.*.network' => ['required_with:socials', 'string', 'max:50'],
            'socials.*.url' => ['required_with:socials', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array{name: string, slug: string, role: string, bio: string|null, socials: array<int, array{network: string, url: string}>|null, sort_order: int, is_active: bool}
     */
    public function payload(): array
    {
        /** @var array{name: string, slug: string, role: string, bio: string|null, socials: array<int, array{network: string, url: string}>|null} $data */
        $data = $this->safe()->except(['photo', 'sort_order', 'is_active']);

        return [
            ...$data,
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}

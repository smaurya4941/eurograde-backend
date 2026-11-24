<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Admin;

use App\Models\TeamMember;
use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Every editable field of a team member, for the admin list/form.
 *
 * @mixin TeamMember
 */
final class AdminTeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'role' => $this->role,
            'bio' => $this->bio,
            'photo_url' => MediaUrl::from($this->photo_path),
            'socials' => $this->socials ?? [],
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

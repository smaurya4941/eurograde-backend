<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\TeamMember;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

final class TeamMemberAdminService
{
    /**
     * @return LengthAwarePaginator<int, TeamMember>
     */
    public function list(int $page, int $perPage): LengthAwarePaginator
    {
        return TeamMember::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $photo): TeamMember
    {
        if ($photo !== null) {
            $data['photo_path'] = $photo->store('team', 'public');
        }

        return TeamMember::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TeamMember $teamMember, array $data, ?UploadedFile $photo): TeamMember
    {
        if ($photo !== null) {
            if ($teamMember->photo_path !== null) {
                Storage::disk('public')->delete($teamMember->photo_path);
            }
            $data['photo_path'] = $photo->store('team', 'public');
        }

        $teamMember->update($data);

        return $teamMember;
    }

    public function delete(TeamMember $teamMember): void
    {
        if ($teamMember->photo_path !== null) {
            Storage::disk('public')->delete($teamMember->photo_path);
        }

        $teamMember->delete();
    }
}

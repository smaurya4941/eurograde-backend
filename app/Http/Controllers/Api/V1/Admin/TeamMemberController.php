<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTeamMemberRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTeamMemberRequest;
use App\Http\Resources\V1\Admin\AdminTeamMemberResource;
use App\Http\Responses\ApiResponse;
use App\Models\TeamMember;
use App\Services\Admin\TeamMemberAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TeamMemberController extends Controller
{
    public function __construct(private readonly TeamMemberAdminService $teamMembers) {}

    public function index(Request $request): JsonResponse
    {
        $teamMembers = $this->teamMembers->list(
            page: $request->integer('page', 1),
            perPage: min($request->integer('per_page', 15), 100),
        );

        return ApiResponse::success(AdminTeamMemberResource::collection($teamMembers));
    }

    public function show(TeamMember $teamMember): JsonResponse
    {
        return ApiResponse::success(new AdminTeamMemberResource($teamMember));
    }

    public function store(StoreTeamMemberRequest $request): JsonResponse
    {
        $teamMember = $this->teamMembers->create($request->payload(), $request->file('photo'));

        return ApiResponse::created(new AdminTeamMemberResource($teamMember), 'Team member created.');
    }

    public function update(UpdateTeamMemberRequest $request, TeamMember $teamMember): JsonResponse
    {
        $teamMember = $this->teamMembers->update($teamMember, $request->payload(), $request->file('photo'));

        return ApiResponse::success(new AdminTeamMemberResource($teamMember), 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember): JsonResponse
    {
        $this->teamMembers->delete($teamMember);

        return ApiResponse::noContent();
    }
}

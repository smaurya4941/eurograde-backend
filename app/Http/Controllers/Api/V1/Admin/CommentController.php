<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UpdateCommentStatusRequest;
use App\Http\Resources\V1\Admin\AdminPostCommentResource;
use App\Http\Responses\ApiResponse;
use App\Models\PostComment;
use App\Services\Admin\CommentAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class CommentController extends Controller
{
    public function __construct(private readonly CommentAdminService $comments) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', Rule::enum(CommentStatus::class)],
        ]);

        $status = $request->filled('status') ? CommentStatus::from($request->string('status')->toString()) : null;

        $comments = $this->comments->list(
            status: $status,
            page: $request->integer('page', 1),
            perPage: min($request->integer('per_page', 20), 100),
        );

        return ApiResponse::success(AdminPostCommentResource::collection($comments));
    }

    public function update(UpdateCommentStatusRequest $request, PostComment $comment): JsonResponse
    {
        $comment = $this->comments->updateStatus($comment, $request->status());

        return ApiResponse::success(new AdminPostCommentResource($comment), 'Comment updated.');
    }

    public function destroy(PostComment $comment): JsonResponse
    {
        $this->comments->delete($comment);

        return ApiResponse::noContent();
    }
}

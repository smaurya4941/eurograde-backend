<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\StorePostCommentRequest;
use App\Http\Resources\V1\PostCommentResource;
use App\Http\Responses\ApiResponse;
use App\Services\Blog\PostCommentService;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class PostCommentController extends Controller
{
    private const PENDING_MESSAGE = 'Thanks for your comment! It will appear once it has been reviewed.';

    private const PUBLISHED_MESSAGE = 'Thanks for your comment! It is now live.';

    public function __construct(
        private readonly ContentService $content,
        private readonly PostCommentService $comments,
    ) {}

    /** Approved comments on a published article, threaded (oldest first). */
    public function index(string $slug): JsonResponse
    {
        ['comments' => $comments, 'total' => $total] = $this->comments->threadFor($this->content->post($slug));

        return ApiResponse::success(PostCommentResource::collection($comments), meta: ['total' => $total]);
    }

    public function store(StorePostCommentRequest $request, string $slug): JsonResponse
    {
        $post = $this->content->post($slug);

        // Honeypot triggered: answer like a held comment so bots learn nothing.
        if ($request->isSpam()) {
            return ApiResponse::created(message: self::PENDING_MESSAGE);
        }

        $comment = $this->comments->submit($post, $request->payload(), $request->ip(), $request->userAgent());

        return $comment->isApproved()
            ? ApiResponse::created(new PostCommentResource($comment), self::PUBLISHED_MESSAGE)
            : ApiResponse::created(message: self::PENDING_MESSAGE);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StorePostRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePostRequest;
use App\Http\Resources\V1\Admin\AdminPostResource;
use App\Http\Responses\ApiResponse;
use App\Models\Post;
use App\Services\Admin\PostAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PostController extends Controller
{
    public function __construct(private readonly PostAdminService $posts) {}

    public function index(Request $request): JsonResponse
    {
        $posts = $this->posts->list(
            page: $request->integer('page', 1),
            perPage: min($request->integer('per_page', 15), 100),
        );

        return ApiResponse::success(AdminPostResource::collection($posts));
    }

    public function show(Post $post): JsonResponse
    {
        return ApiResponse::success(new AdminPostResource($post->load(['category', 'author', 'tags'])));
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->posts->create($request->payload(), $request->file('image'), $request->tagIds());

        return ApiResponse::created(new AdminPostResource($post->load('tags')), 'Post created.');
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $post = $this->posts->update($post, $request->payload(), $request->file('image'), $request->tagIds());

        return ApiResponse::success(new AdminPostResource($post->load('tags')), 'Post updated.');
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->posts->delete($post);

        return ApiResponse::noContent();
    }
}

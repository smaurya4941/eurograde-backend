<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StorePostCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePostCategoryRequest;
use App\Http\Resources\V1\PostCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\PostCategory;
use App\Services\Admin\PostCategoryAdminService;
use Illuminate\Http\JsonResponse;

/** Every category (not just ones with published posts), for the post-form dropdown and admin CRUD. */
final class PostCategoryController extends Controller
{
    public function __construct(private readonly PostCategoryAdminService $categories) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(PostCategoryResource::collection($this->categories->list()));
    }

    public function store(StorePostCategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->payload());

        return ApiResponse::created(new PostCategoryResource($category), 'Category created.');
    }

    public function update(UpdatePostCategoryRequest $request, PostCategory $postCategory): JsonResponse
    {
        $category = $this->categories->update($postCategory, $request->payload());

        return ApiResponse::success(new PostCategoryResource($category), 'Category updated.');
    }

    public function destroy(PostCategory $postCategory): JsonResponse
    {
        $this->categories->delete($postCategory);

        return ApiResponse::noContent();
    }
}

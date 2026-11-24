<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTagRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTagRequest;
use App\Http\Resources\V1\TagResource;
use App\Http\Responses\ApiResponse;
use App\Models\Tag;
use App\Services\Admin\TagAdminService;
use Illuminate\Http\JsonResponse;

final class TagController extends Controller
{
    public function __construct(private readonly TagAdminService $tags) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(TagResource::collection($this->tags->list()));
    }

    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tags->create($request->payload());

        return ApiResponse::created(new TagResource($tag), 'Tag created.');
    }

    public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
    {
        $tag = $this->tags->update($tag, $request->payload());

        return ApiResponse::success(new TagResource($tag), 'Tag updated.');
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->tags->delete($tag);

        return ApiResponse::noContent();
    }
}

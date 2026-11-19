<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PostCategoryResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class PostCategoryController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    /** Blog categories that contain published posts, with post counts. */
    public function index(): JsonResponse
    {
        return ApiResponse::success(PostCategoryResource::collection($this->content->postCategories()));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\LimitRequest;
use App\Http\Resources\V1\SuccessStoryResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class SuccessStoryController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function index(LimitRequest $request): JsonResponse
    {
        return ApiResponse::success(
            SuccessStoryResource::collection($this->content->successStories($request->limit())),
        );
    }
}

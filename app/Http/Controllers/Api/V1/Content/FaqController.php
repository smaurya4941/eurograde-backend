<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\IndexFaqRequest;
use App\Http\Resources\V1\FaqResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class FaqController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function index(IndexFaqRequest $request): JsonResponse
    {
        return ApiResponse::success(
            FaqResource::collection($this->content->faqs(...$request->categories())),
        );
    }
}

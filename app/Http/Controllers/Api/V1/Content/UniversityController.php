<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\IndexUniversityRequest;
use App\Http\Resources\V1\UniversityResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class UniversityController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function index(IndexUniversityRequest $request): JsonResponse
    {
        return ApiResponse::success(UniversityResource::collection($this->content->universities(
            $request->country(),
            $request->partnersOnly(),
            $request->limit(),
        )));
    }
}

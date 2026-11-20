<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CountryDetailResource;
use App\Http\Resources\V1\CountryResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class CountryController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(CountryResource::collection($this->content->countries()));
    }

    public function show(string $slug): JsonResponse
    {
        return ApiResponse::success(new CountryDetailResource($this->content->country($slug)));
    }
}

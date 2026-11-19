<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class ServiceController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(ServiceResource::collection($this->content->services()));
    }
}

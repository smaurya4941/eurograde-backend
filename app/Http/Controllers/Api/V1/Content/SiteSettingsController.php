<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\SiteSettingsResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class SiteSettingsController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function __invoke(): JsonResponse
    {
        return ApiResponse::success(new SiteSettingsResource($this->content->siteSettings()));
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\IndexTestimonialRequest;
use App\Http\Resources\V1\TestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class TestimonialController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    public function index(IndexTestimonialRequest $request): JsonResponse
    {
        return ApiResponse::success(
            TestimonialResource::collection($this->content->testimonials(
                $request->categories(),
                $request->featuredOnly(),
                $request->limit(),
            )),
        );
    }
}

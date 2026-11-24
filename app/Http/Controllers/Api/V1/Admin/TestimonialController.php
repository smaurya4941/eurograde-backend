<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTestimonialRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTestimonialRequest;
use App\Http\Resources\V1\Admin\AdminTestimonialResource;
use App\Http\Responses\ApiResponse;
use App\Models\Testimonial;
use App\Services\Admin\TestimonialAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TestimonialController extends Controller
{
    public function __construct(private readonly TestimonialAdminService $testimonials) {}

    public function index(Request $request): JsonResponse
    {
        $testimonials = $this->testimonials->list(
            page: $request->integer('page', 1),
            perPage: min($request->integer('per_page', 15), 100),
        );

        return ApiResponse::success(AdminTestimonialResource::collection($testimonials));
    }

    public function show(Testimonial $testimonial): JsonResponse
    {
        return ApiResponse::success(new AdminTestimonialResource($testimonial));
    }

    public function store(StoreTestimonialRequest $request): JsonResponse
    {
        $testimonial = $this->testimonials->create($request->payload(), $request->file('avatar'));

        return ApiResponse::created(new AdminTestimonialResource($testimonial), 'Testimonial created.');
    }

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): JsonResponse
    {
        $testimonial = $this->testimonials->update($testimonial, $request->payload(), $request->file('avatar'));

        return ApiResponse::success(new AdminTestimonialResource($testimonial), 'Testimonial updated.');
    }

    public function destroy(Testimonial $testimonial): JsonResponse
    {
        $this->testimonials->delete($testimonial);

        return ApiResponse::noContent();
    }
}

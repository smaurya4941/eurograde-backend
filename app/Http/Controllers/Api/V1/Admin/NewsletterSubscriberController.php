<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Admin\AdminNewsletterSubscriberResource;
use App\Http\Responses\ApiResponse;
use App\Models\NewsletterSubscriber;
use App\Services\Admin\NewsletterSubscriberAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NewsletterSubscriberController extends Controller
{
    public function __construct(private readonly NewsletterSubscriberAdminService $subscribers) {}

    /** ?subscribed=1|0 filters; omitted shows all. */
    public function index(Request $request): JsonResponse
    {
        $subscribed = $request->has('subscribed') ? $request->boolean('subscribed') : null;

        $subscribers = $this->subscribers->list(
            page: $request->integer('page', 1),
            perPage: min($request->integer('per_page', 15), 100),
            subscribed: $subscribed,
        );

        return ApiResponse::success(AdminNewsletterSubscriberResource::collection($subscribers));
    }

    public function destroy(NewsletterSubscriber $subscriber): JsonResponse
    {
        $this->subscribers->delete($subscriber);

        return ApiResponse::noContent();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Admin\AdminContactMessageResource;
use App\Http\Responses\ApiResponse;
use App\Models\ContactMessage;
use App\Services\Admin\ContactMessageAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ContactMessageController extends Controller
{
    public function __construct(private readonly ContactMessageAdminService $messages) {}

    /** ?handled=1|0 filters; omitted shows all. */
    public function index(Request $request): JsonResponse
    {
        $handled = $request->has('handled') ? $request->boolean('handled') : null;

        $messages = $this->messages->list(
            page: $request->integer('page', 1),
            perPage: min($request->integer('per_page', 15), 100),
            handled: $handled,
        );

        return ApiResponse::success(AdminContactMessageResource::collection($messages));
    }

    public function update(Request $request, ContactMessage $message): JsonResponse
    {
        $message = $this->messages->markHandled($message, $request->boolean('handled', true));

        return ApiResponse::success(new AdminContactMessageResource($message), 'Updated.');
    }

    public function destroy(ContactMessage $message): JsonResponse
    {
        $this->messages->delete($message);

        return ApiResponse::noContent();
    }
}

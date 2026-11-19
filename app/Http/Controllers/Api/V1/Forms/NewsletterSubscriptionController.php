<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Forms\StoreNewsletterSubscriptionRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Forms\NewsletterService;
use Illuminate\Http\JsonResponse;

final class NewsletterSubscriptionController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter) {}

    public function store(StoreNewsletterSubscriptionRequest $request): JsonResponse
    {
        $result = $this->newsletter->subscribe(
            email: (string) $request->validated('email'),
            sourcePage: $request->validated('source_page'),
            ipAddress: $request->ip(),
        );

        return $result['created']
            ? ApiResponse::created(message: 'Thanks for subscribing! Look out for our next update.')
            : ApiResponse::success(message: 'You’re already subscribed — thanks for staying with us.');
    }
}

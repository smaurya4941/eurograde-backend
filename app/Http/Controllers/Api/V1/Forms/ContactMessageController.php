<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Forms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Forms\StoreContactMessageRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Forms\ContactMessageService;
use Illuminate\Http\JsonResponse;

final class ContactMessageController extends Controller
{
    private const SUCCESS_MESSAGE = 'Thank you! Your message has been sent. Our team will get back to you within one business day.';

    public function __construct(private readonly ContactMessageService $messages) {}

    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        // Honeypot triggered: answer exactly like a success so bots learn nothing.
        if ($request->isSpam()) {
            return ApiResponse::created(message: self::SUCCESS_MESSAGE);
        }

        $this->messages->submit($request->payload(), $request->ip(), $request->userAgent());

        return ApiResponse::created(message: self::SUCCESS_MESSAGE);
    }
}

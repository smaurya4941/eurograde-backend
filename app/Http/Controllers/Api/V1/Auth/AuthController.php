<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Responses\ApiResponse;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->auth->register($request->validated(), $request->session());

        return ApiResponse::created(new UserResource($user), 'Registration successful.');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->auth->login(
            credentials: $request->safe()->only(['email', 'password']),
            remember: $request->boolean('remember'),
            session: $request->session(),
            throttleKey: $request->throttleKey(),
        );

        return ApiResponse::success(new UserResource($user), 'Logged in.');
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $this->auth->logout($request->session());

        return ApiResponse::success(message: 'Logged out.');
    }
}

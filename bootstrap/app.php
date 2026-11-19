<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cookie/session based auth for our own SPA (Sanctum).
        $middleware->statefulApi();

        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $isApi = static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($isApi);

        // Order matters: the first callback that returns a response wins.
        $exceptions->render(function (ValidationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return ApiResponse::error($e->getMessage(), $e->status, $e->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return ApiResponse::error('Unauthenticated.', 401);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return ApiResponse::error('Resource not found.', 404);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                $message = $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.';

                return ApiResponse::error($message, $e->getStatusCode())
                    ->withHeaders($e->getHeaders());
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                $errors = config('app.debug') ? [
                    'exception' => [get_class($e)],
                    'detail' => [$e->getMessage()],
                    'location' => [$e->getFile().':'.$e->getLine()],
                ] : null;

                return ApiResponse::error('Server error.', 500, $errors);
            }
        });
    })->create();

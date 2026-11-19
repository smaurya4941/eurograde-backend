<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * Single source of truth for the API's JSON envelope.
 *
 * Success: { "success": true,  "message": "...", "data": ..., "meta": {...} }
 * Error:   { "success": false, "message": "...", "errors": {...} }
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = Response::HTTP_OK,
        array $meta = [],
    ): JsonResponse {
        if ($data instanceof JsonResource) {
            // Unwrap resources. Paginated collections come back as
            // { data, links: {first,last,prev,next}, meta: {current_page, total, links: [...]} };
            // flatten that into a single `meta` object: { current_page, last_page, per_page, total, ..., links }.
            $payload = $data->response()->getData(true);
            $data = $payload['data'] ?? $payload;

            $pagination = Arr::except(is_array($payload['meta'] ?? null) ? $payload['meta'] : [], ['links']);
            $links = isset($payload['links']) ? ['links' => $payload['links']] : [];

            $meta = array_merge($pagination, $links, Arr::except($payload, ['data', 'meta', 'links']), $meta);
        }

        return response()->json(array_filter([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta ?: null,
        ], static fn ($value) => $value !== null), $status);
    }

    public static function created(mixed $data = null, ?string $message = 'Created.'): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public static function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
    ): JsonResponse {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], static fn ($value) => $value !== null), $status);
    }
}

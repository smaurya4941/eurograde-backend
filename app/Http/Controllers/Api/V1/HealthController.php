<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            $database = 'up';
        } catch (Throwable) {
            $database = 'down';
        }

        $healthy = $database === 'up';

        return ApiResponse::success(
            data: [
                'status' => $healthy ? 'ok' : 'degraded',
                'database' => $database,
                'time' => now()->toIso8601String(),
            ],
            status: $healthy ? 200 : 503,
        );
    }
}

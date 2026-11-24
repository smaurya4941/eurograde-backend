<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Restricts a route to authenticated users with the `is_admin` flag set. */
final class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->is_admin, Response::HTTP_FORBIDDEN, 'Forbidden.');

        return $next($request);
    }
}

<?php

use Illuminate\Support\Facades\Route;

/*
| This application is API-only; the UI is served by the React app in /frontend.
| Sanctum registers GET /sanctum/csrf-cookie on the web middleware group.
*/

Route::get('/', fn () => response()->json([
    'name' => config('app.name'),
    'api' => url('/api/v1'),
]));

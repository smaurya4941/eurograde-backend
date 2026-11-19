<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Every route is versioned. Add a new file (e.g. routes/api/v2.php) and a
| new group below when introducing breaking changes.
*/

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware('throttle:api')
    ->group(base_path('routes/api/v1.php'));

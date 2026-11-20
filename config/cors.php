<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | In local dev the React app calls the API through the Vite proxy (same
    | origin), so CORS is not exercised. These settings apply when the SPA
    | and API live on different origins (e.g. app.example.com / api.example.com).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_map('trim', explode(',', (string) env('FRONTEND_URL', 'http://localhost:5173')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required for cookie-based Sanctum SPA authentication.
    'supports_credentials' => true,

];

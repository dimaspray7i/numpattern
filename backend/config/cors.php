<?php
// config/cors.php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    | Allows the frontend (served from a different origin) to communicate
    | with this API. Adjust 'allowed_origins' to match your frontend URL.
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5500',    // VS Code Live Server
        'http://127.0.0.1:5500',
        'http://localhost:3000',    // Any dev server
        'http://127.0.0.1:3000',
        // Add your production frontend URL here, e.g.:
        // 'https://numpattern.yourdomain.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Must be true for Sanctum cookie-based auth (not needed for token auth)
    'supports_credentials' => true,
];

<?php
// config/sanctum.php

return [
    /*
    |--------------------------------------------------------------------------
    | Sanctum Configuration
    |--------------------------------------------------------------------------
    */

    // Domains that are allowed to use cookie-based auth (not needed for token auth)
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', implode(',', [
        'localhost',
        'localhost:3000',
        'localhost:5500',
        '127.0.0.1',
        '127.0.0.1:8000',
        '127.0.0.1:5500',
        env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : null,
    ]))),

    'guard' => ['web'],

    // Token expiration: null = never expire; set to minutes for security
    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies'      => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token'  => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];

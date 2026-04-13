<?php

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    // JWT token TTL in seconds (default: 1 hour)
    'token_ttl' => env('AUTH_TOKEN_TTL', 3600),

    // Refresh token TTL in seconds (default: 7 days)
    'refresh_ttl' => env('AUTH_REFRESH_TTL', 604800),

];

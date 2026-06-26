<?php
// Configuration file
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'first_api',
        'username' => 'root',
        'password' => '',
    ],
    'security' => [
        'jwt_secret' => getenv('JWT_SECRET') ?: 'first_api_jwt_secret_key_2024_secure_and_unique',
        'jwt_expiration' => 3600, // 1 hour
        'password_min_length' => 8,
        'password_max_length' => 128,
        'password_requirements' => [
            'uppercase' => true,
            'lowercase' => true,
            'number' => true,
            'special_char' => true,
        ],
    ],
    'rate_limit' => [
        'max_requests' => 100,
        'time_window' => 3600, // seconds
    ],
    'csrf' => [
        'token_name' => '_csrf_token',
        'header_name' => 'X-CSRF-TOKEN',
    ],
    'log' => [
        'path' => __DIR__ . '/../logs/api.log',
        'level' => 'debug',
    ],
];

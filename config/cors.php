<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        static fn (string $origin): string => rtrim(trim($origin), '/'),
        explode(',', (string) env(
            'CORS_ALLOWED_ORIGINS',
            'https://mobilriccarton.com,https://www.mobilriccarton.com,https://api-v1.mb.softsove.life,http://localhost:3000,http://127.0.0.1:3000,http://192.168.1.61:3000,http://192.168.1.25:3000'
        ))
    ))),

    'allowed_origins_patterns' => [
        '#^https?://(www\.)?mobilriccarton\.com$#',
        '#^https?://api-v1\.mb\.softsove\.life$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,

];

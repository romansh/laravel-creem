<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Creem Configuration Profiles
    |--------------------------------------------------------------------------
    |
    | Define multiple profiles for different use cases or environments.
    | Each profile contains API credentials and test mode settings.
    |
    */

    'profiles' => [
        'default' => [
            'api_key' => env('CREEM_API_KEY'),
            'test_mode' => env('CREEM_TEST_MODE', false),
            'webhook_secret' => env('CREEM_WEBHOOK_SECRET'),
        ],

        // Example: Separate profile for different products or services
        // 'product_a' => [
        //     'api_key' => env('CREEM_PRODUCT_A_KEY'),
        //     'test_mode' => true,
        //     'webhook_secret' => env('CREEM_PRODUCT_A_WEBHOOK_SECRET'),
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook routing and verification settings.
    |
    */

    'webhook' => [
        'path' => '/creem/webhook',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Configuration
    |--------------------------------------------------------------------------
    |
    | Configure HTTP client settings like timeouts and retries.
    |
    */

    'http' => [
        'timeout' => 30,
        'retry' => [
            'times' => 3,
            'sleep' => 100,
        ],
    ],
];

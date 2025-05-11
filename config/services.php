<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET_KEY'),
    ],

    'pearls' => [
        'custom' => [
            'package' => 'custom',
            'price' => 15,
            'limit_count' => 1,
            'price_per_post' => 15,
            'savings' => 0,       // (10 - 9.5) × 50 = 25 GEL savings
        ],
        'small' => [
            'package' => 'small',
            'price' => 190,        // 50 posts × 9.5 GEL
            'limit_count' => 20,
            'price_per_post' => 9.5,
            'savings' => 10,       // (10 - 9.5) × 50 = 25 GEL savings
        ],
        'medium' => [
            'package' => 'medium',
            'price' => 475,        // 100 posts × 9 GEL
            'limit_count' => 50,
            'price_per_post' => 9.5,
            'savings' => 25,      // Less attractive savings
        ],
        'premium' => [
            'package' => 'premium',
            'price' => 950,       // 250 posts × 8 GEL
            'limit_count' => 100,
            'price_per_post' => 9.5,
            'savings' => 50,      // Medium savings
        ],
        'pro' => [
            'package' => 'Pro',
            'price' => 1425,       // 250 posts × 8 GEL
            'limit_count' => 150,
            'price_per_post' => 8,
            'savings' => 75,      // Medium savings
        ]
    ],

];

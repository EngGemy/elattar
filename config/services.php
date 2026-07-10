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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'storefront' => [
        'whatsapp' => env('STOREFRONT_WHATSAPP', '201000000000'),

        'payment' => [
            'instapay'      => env('STOREFRONT_INSTAPAY', '01234567890'),
            'vodafone_cash' => env('STOREFRONT_VODAFONE_CASH', '01012345678'),
        ],

        'delivery' => [
            'governorate' => 'الدقهلية',
            'cities'      => ['المنصورة', 'طلخا'],
        ],
    ],

];

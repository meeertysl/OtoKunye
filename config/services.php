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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'netgsm' => [
        'enabled' => env('NETGSM_ENABLED', false),
        'url' => env('NETGSM_URL', 'https://api.netgsm.com.tr/sms/send/get'),
        'usercode' => env('NETGSM_USERCODE'),
        'password' => env('NETGSM_PASSWORD'),
        'header' => env('NETGSM_HEADER'),
        'language' => env('NETGSM_LANGUAGE', 'TR'),
        'timeout' => env('NETGSM_TIMEOUT', 15),
    ],

    'parasut' => [
        'enabled' => env('PARASUT_ENABLED', false),
        'api_url' => env('PARASUT_API_URL', 'https://api.parasut.com/v4'),
        'api_key' => env('PARASUT_API_KEY'),
        'company_id' => env('PARASUT_COMPANY_ID'),
    ],

];

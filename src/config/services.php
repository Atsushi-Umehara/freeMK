<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe（FN023）
    |--------------------------------------------------------------------------
    |
    | Stripe決済・Webhookで使用する設定
    |
    */

    'stripe' => [
        // サーバー側で使う Secret Key（sk_test_...）
        'secret' => env('STRIPE_SECRET'),

        // フロント側で使う Publishable Key（pk_test_...）
        'public' => env('STRIPE_PUBLIC'),

        // Webhook署名検証用（whsec_...）
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
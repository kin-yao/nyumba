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
    
    'africastalking' => [
    'username' => env('AT_USERNAME', 'sandbox'),
    'api_key'  => env('AT_API_KEY', ''),
    'from'     => env('AT_FROM', 'Nyumba'),
],

    'mpesa' => [
        'consumer_key'    => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode'      => env('MPESA_SHORTCODE', '174379'),
        'passkey'        => env('MPESA_PASSKEY'),
        'env'            => env('MPESA_ENV', 'sandbox'), // or 'production'
    ],

    'kcb' => [
        // KCB's public key used to verify the SHA256withRSA `Signature` header
        // on every IPN notification (PEM format, account-wide, not per-property).
        'ipn_public_key' => env('KCB_IPN_PUBLIC_KEY'),

        // TEMPORARY toggle — false disables signature verification entirely.
        // Defaults to true (secure) if unset, so forgetting to set this var
        // fails safe rather than fails open.
        'verify_signature' => env('KCB_IPN_VERIFY_SIGNATURE', true),
    ],

    'firebase' => [
        // Firebase Web API keys are public by design (same one used
        // client-side on the login page) — used server-side only to verify
        // a "current password" via the Identity Toolkit REST API.
        'web_api_key' => env('FIREBASE_WEB_API_KEY', 'AIzaSyCwVY3ZvJajNwF6KFOsENqnmwHUHjCUZ6U'),
    ],

];

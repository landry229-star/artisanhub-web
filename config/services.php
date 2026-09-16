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
    'fedapay' => [
    'public_key'      => env('FEDAPAY_PUBLIC_KEY'),
    'secret_key'      => env('FEDAPAY_SECRET_KEY'),
    'environment'     => env('FEDAPAY_ENV', 'sandbox'),
    'webhook_secret'  => env('FEDAPAY_WEBHOOK_SECRET'),
],

    'whatsapp' => [
        // 'log' (dev, écrit dans laravel.log), 'meta' (WhatsApp Cloud API), 'sms' (passerelle générique)
        'driver'   => env('WHATSAPP_DRIVER', 'log'),
        'token'    => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
        'sms_url'  => env('SMS_GATEWAY_URL'),
        'sms_key'  => env('SMS_GATEWAY_KEY'),
    ],

    'translation' => [
        // 'none' (défaut, désactivé), 'anthropic', ou 'libretranslate'
        'driver'               => env('TRANSLATION_DRIVER', 'none'),
        'anthropic_key'        => env('ANTHROPIC_API_KEY'),
        'libretranslate_url'   => env('LIBRETRANSLATE_URL'),
        'libretranslate_key'   => env('LIBRETRANSLATE_KEY'),
    ],

    'transcription' => [
        // 'none' (défaut, désactivé) ou 'openai_whisper'
        'driver'      => env('TRANSCRIPTION_DRIVER', 'none'),
        'openai_key'  => env('OPENAI_API_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
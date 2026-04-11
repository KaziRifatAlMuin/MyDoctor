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

    'webpush' => [
    'vapid' => [
        'subject' => env('VAPID_SUBJECT'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],
    'group_size' => 500, // Number of notifications to send at once
],

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

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('AI_CHAT_MODEL', 'google/gemini-2.0-flash-001'),
        'fallback_models' => array_values(array_filter([
            env('AI_CHAT_FALLBACK_MODEL_1', 'openai/gpt-4o-mini'),
            env('AI_CHAT_FALLBACK_MODEL_2', 'anthropic/claude-3.5-haiku'),
            env('AI_CHAT_FALLBACK_MODEL_3', 'meta-llama/llama-3.1-70b-instruct'),
        ])),
        'site_url' => env('OPENROUTER_SITE_URL', env('APP_URL', 'http://localhost')),
        'app_name' => env('OPENROUTER_APP_NAME', env('APP_NAME', 'MyDoctor')),
    ],

    'google' => [
        'api_key' => env('GOOGLE_API_KEY'),
        'model' => env('GOOGLE_MODEL', 'gemini-1.5-flash'),
        'fallback_models' => array_values(array_filter([
            env('GOOGLE_FALLBACK_MODEL_1', 'gemini-1.5-flash-latest'),
            env('GOOGLE_FALLBACK_MODEL_2', 'gemini-1.5-pro'),
            env('GOOGLE_FALLBACK_MODEL_3', 'gemini-2.0-flash'),
        ])),
    ],

];

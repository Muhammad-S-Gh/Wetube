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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => env('OPENAI_TIMEOUT', 8),
    ],

    'pagination' => [
        'videos' => env('VIDEOS_PER_PAGE'),
    ],

    'hugging_face' => [
        'key' => env('HUGGING_FACE_API_KEY'),
        'base_url' => env('HUGGING_FACE_BASE_URL', env('BASE_URL', 'https://api-inference.huggingface.co/models')),
        'timeout' => env('HUGGING_FACE_TIMEOUT', env('TIMEOUT', 8)),
        'image_nsfw_threshold' => (float) env('HUGGING_FACE_IMAGE_NSFW_THRESHOLD', 0.35),
        //
        'text_toxic_threshold' => (float) env('HUGGING_FACE_TEXT_TOXIC_THRESHOLD', 0.85),
        'text_threat_threshold' => (float) env('HUGGING_FACE_TEXT_THREAT_THRESHOLD', 0.85),
        'text_identity_hate_threshold' => (float) env('HUGGING_FACE_TEXT_IDENTITY_HATE_THRESHOLD', 0.85),
        'text_obscene_threshold' => (float) env('HUGGING_FACE_TEXT_OBSCENE_THRESHOLD', 0.85),
        'text_insult_threshold' => (float) env('HUGGING_FACE_TEXT_INSULT_THRESHOLD', 0.85),
    ],
];

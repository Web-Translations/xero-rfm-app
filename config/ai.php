<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AI-powered insights and narrative generation
    |
    */

    'provider' => env('AI_PROVIDER', 'openai'),
    
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],
    
    'insights' => [
        'enabled' => env('AI_INSIGHTS_ENABLED', true),
        'cache_duration' => env('AI_INSIGHTS_CACHE_DURATION', 3600), // 1 hour
        'fallback_to_deterministic' => env('AI_INSIGHTS_FALLBACK', true),
    ],
];

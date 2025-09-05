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
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        'org' => env('OPENAI_ORG'),
        'project' => env('OPENAI_PROJECT'),
    ],
    
    'insights' => [
        'enabled' => env('AI_INSIGHTS_ENABLED', true),
        'cache_duration' => env('AI_INSIGHTS_CACHE_DURATION', 3600), // 1 hour
        // Set to false to surface OpenAI errors instead of falling back silently
        'fallback_to_deterministic' => env('AI_INSIGHTS_FALLBACK', false),
    ],
];

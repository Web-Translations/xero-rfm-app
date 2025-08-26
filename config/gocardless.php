<?php

return [
    /*
    |--------------------------------------------------------------------------
    | GoCardless Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for GoCardless integration.
    | You can find your access token in your GoCardless dashboard.
    |
    */

    'access_token' => env('GOCARDLESS_ACCESS_TOKEN', ''),
    
    'environment' => env('GOCARDLESS_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'live'
    
    'webhook_secret' => env('GOCARDLESS_WEBHOOK_SECRET', ''),
    'creditor_id' => env('GOCARDLESS_CREDITOR_ID', ''),
    'success_url' => env('GOCARDLESS_SUCCESS_URL', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Define your subscription plans with their GoCardless plan IDs
    |
    */
    
    'plans' => [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'currency' => 'GBP',
            'interval' => 'monthly',
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 599, // £5.99 in pence
            'currency' => 'GBP',
            'interval' => 'monthly',
        ],
        'pro_plus' => [
            'name' => 'Pro+',
            'price' => 1199, // £11.99 in pence
            'currency' => 'GBP',
            'interval' => 'monthly',
        ],
    ],
];

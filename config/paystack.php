<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paystack Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Paystack payment gateway.
    | You can get your keys from your Paystack dashboard.
    |
    */

    'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
    'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
    'callback_url' => env('PAYSTACK_CALLBACK_URL', ''),
    'webhook_url' => env('PAYSTACK_WEBHOOK_URL', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */
    
    'currency' => env('PAYSTACK_CURRENCY', 'NGN'),
    'split_code' => env('PAYSTACK_SPLIT_CODE', ''), // Optional: for sub-account splitting
    
    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    */
    
    'environment' => env('PAYSTACK_ENVIRONMENT', 'test'), // 'test' or 'live'
    
    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */
    
    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
];

<?php

use App\Services\SalesLVService;
use App\Services\TwilioService;

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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'recaptcha' => [
        'sitekey' => env('RECAPTCHA_SITEKEY'),
        'secret' => env('RECAPTCHA_SECRET'),
        'no_hide' => env('RECAPTCHA_NO_HIDE_INPUT') ?? false,
    ],

    'kraken' => [
        'api_key' => env('KRAKEN_API_KEY'),
        'api_secret' => env('KRAKEN_API_SECRET'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_AACCOUNT_SID'),
        'token' => env('TWILIO_TOKEN'),
        'service_sid' => env('TWILIO_SERVICE_SID'),
        'sender_id' => env('TWILIO_SERVICE_ALPHANUMERIC_SENDER_ID'),
        'enabled' => env('TWILIO_ENABLED', true),
    ],

    'saleslv' => [
        'api_key' => env('SALES_LV_API_KEY'),
        'service_sid' => env('SALES_LV_SERVICE_SID'),
        'enabled' => env('SALES_LV_ENABLED', false),
    ],

    'enabled_sms_providers' => [
        'saleslv' => SalesLVService::class,
        'twilio' => TwilioService::class,
    ],

    'bitgo' => [
        'coin_prefix' => env('BITGO_COIN_PREFIX'),
    ],
];

<?php

use App\Enums\WallesterCardTypes;

return [

    /*
    |--------------------------------------------------------------------------
    | Cratos business vars
    |--------------------------------------------------------------------------
    */

    'urls' => [
        'theme' => env('CRATOS_THEME_URL'), // relative
        'landing' => env('CRATOS_LANDING_URL'), // absolute w/o scheme
        'cabinet' => [
            'api-v1' => '/ajax/', // absolute w/o scheme
        ],
        'terms_and_conditions' => env('TERMS_AND_CONDITIONS_URL'),
        'aml_policy' => env('AML_POLICY_URL'),
        'privacy_policy' => env('PRIVACY_POLICY_URL'),
        'frequently_asked_question' => env('FREQUENTLY_ASKED_QUESTION'),
    ],

    'testing' => [
        'default_password' => env('CRATOS_TESTING_DEFAULT_PASSWORD'),
        'cuser_count' => env('CRATOS_TESTING_CUSER_COUNT', 1000),
        'admin_login' => env('CRATOS_TESTING_ADMIN_LOGIN', 'admintest@gmail.com'),
        'skip_i18n' => env('CRATOS_TESTING_SKIP_I18N', false),
    ],

    'params' => [
        'recaptcha_score' => env('CRATOS_RECAPTCHA_SCORE', 0.5),
    ],
    'sum_sub' => [
        'api_url' => env('SUMSUB_API_URL'),
        'app_token' => env('SUMSUB_APP_TOKEN'),
        'secret_key' => env('SUMSUB_SECRET_KEY'),
        'webhook_secret_key' => env('SUMSUB_WEBHOOK_SECRET_KEY'),
        'card_webhook_secret_key' => env('SUMSUB_CARD_WEBHOOK_SECRET_KEY'),
        'individual_level_1' => env('SUMSUB_INDIVIDUAL_LEVEL_1_NAME'),
        'individual_level_2' => env('SUMSUB_INDIVIDUAL_LEVEL_2_NAME'),
        'individual_level_3' => env('SUMSUB_INDIVIDUAL_LEVEL_3_NAME'),
        'corporate_level_1' => env('SUMSUB_CORPORATE_LEVEL_1_NAME'),
        'corporate_level_2' => env('SUMSUB_CORPORATE_LEVEL_2_NAME'),
        'corporate_level_3' => env('SUMSUB_CORPORATE_LEVEL_3_NAME'),
        'old_app_token' => env('OLD_SUMSUB_APP_TOKEN'),
        'old_secret_key' => env('OLD_SUMSUB_SECRET_KEY'),

        'allowed_requests_count' => env('CRATOS_ALLOWED_COMPLIANCE_REQUESTS_COUNT', 5),

        'individual_doc_utility_bill' => env('SUMSUB_INDIVIDUAL_DOC_UTILITY_BILL'),
        'individual_doc_source_of_funds' => env('SUMSUB_INDIVIDUAL_DOC_SOURCE_OF_FUNDS'),

        'make_documents_inactive_after' => env('SUMSUB_MAKE_DOCUMENTS_INACTIVE_AFTER'),
        'additional_time_for_doc_upload' => env('SUMSUB_ADDITIONAL_TIME_FOR_DOC_UPLOAD'),
        'notify_time_before_making_user_suspended' => env('SUMSUB_NOTIFY_TIME_BEFORE_MAKING_USER_SUSPENDED'),


    ],

    'wallester' => [
//        'token' => env('WALLESTER_TOKEN'),
//        'code' => env('WALLESTER_PRODUCT_CODE'),
//        'auditType' => env('WALLESTER_AUDIT_TYPE'),
//        'userId' => env('WALLESTER_USER_ID'),
        'enabled' => env('WALLESTER_ENABLED', false),
        'issuer' => env('WALLESTER_ISSUER_ID'),
        'audience' => env('WALLESTER_AUDIENCE_ID'),
        'appUrl' => env('WALLESTER_APP_URL'),
        'appSite' => env('WALLESTER_APP_SITE'),
        'terms_and_conditions' => env('WALLESTER_TERMS_AND_CONDITIONS', 'https://connectee.io/card-terms'),

    ],

    'accounts' => [
        'risk_score' => 0.7,
        'risk_score_days' => 30,
        'risk_score_days_for_0' => 10
    ],

    'deposit' => [
        'available-extensions' => [
            '.jpg',
            '.png',
            '.pdf',
        ]
    ],

    'pagination' => [
        'notifications' => 10,
        'settings' => 10,
        'operations' => 10,
        'tickets' => 3,
        'accounts' => 12,
    ],

    'bitgo' => [
        'coin-prefix' => env('BITGO_COIN_PREFIX', ''),
        'bitgo_id' => env('BITGO_ID'),
        'walletId' => env('BITGO_WALLET_ID'),
        'token' => env('BITGO_TOKEN'),
        'localhost' => env('BITGO_LOCALHOST'),
    ],

    'compliance' => [
        'expire_period' => 10
    ],

    'chunk' => [
        'report' => 5
    ],


    'enabled_coins' => [
        \App\Enums\Currency::CURRENCY_BTC,
        \App\Enums\Currency::CURRENCY_LTC,
        \App\Enums\Currency::CURRENCY_BCH ,
        \App\Enums\Currency::CURRENCY_ETH,
//        \App\Enums\Currency::CURRENCY_XRP,
        \App\Enums\Currency::CURRENCY_USDT,
        \App\Enums\Currency::CURRENCY_USDC,
        \App\Enums\Currency::CURRENCY_WBTC,
        \App\Enums\Currency::CURRENCY_LN,
//        \App\Enums\Currency::CURRENCY_TRX,
        \App\Enums\Currency::CURRENCY_UNI,
//        \App\Enums\Currency::CURRENCY_DASH,
//        \App\Enums\Currency::CURRENCY_ZEC,
        \App\Enums\Currency::CURRENCY_MCDAI,
    ],

    'company_details' => [
        'name' => 'Sky-mechanics',
        'country' => 'Estonia',
        'city' => 'Tallinn',
        'zip_code' => '110122',
        'address' => 'Suur-Ameerika, Toompuiestee,',
        'license' => 'FFF000001',
        'registry' => '123456789',
        'logo' => env('CRATOS_THEME_URL') . 'images/logo.svg',
    ],
    'history_list_details' => [
        'images' => [
            'img-1' => '',
            'img-2' => '',
            'img-3' => ''
        ],
    ],
    'age_confirmation' => false,
    'automatic_withdrawal' => env('AUTOMATIC_WITHDRAWAL', true),
    'enable_fiat_wallets' => env('ENABLE_FIAT_WALLETS', false),
    'enable_send_notification_sms' => env('ENABLE_SEND_NOTIFICATION_SMS', false),


    'analytic_system' => env('ANALYTIC_SYSTEM'),
    'chainalysis_risk_score' =>[
        'low' => 0.1,
        'medium' => 0.50,
        'high' => 0.75,
        'severe' => 1
    ],

    'chainalysis_api_key' => env('CHAINALYSISIS_API_KEY'),
    'chainalysis_api_url' => env('CHAINALYSISIS_API_URL'),
    'modules' =>[
        'wallester' => env('WALLESTER_ENABLED'),
        'fiat_wallets' => env('ENABLE_FIAT_WALLETS'),
    ],

    'enable_send_notification_sms' => env('ENABLE_SEND_NOTIFICATION_SMS', false),


];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | KYT providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'sum_sub' => [
            'cratos' => [
                'api_url' => env('SUMSUB_API_URL'),
                'app_token' => env('SUMSUB_APP_TOKEN'),
                'secret_key' => env('SUMSUB_SECRET_KEY'),
                'webhook_secret_key' => env('SUMSUB_WEBHOOK_SECRET_KEY'),
                'individual_level_1' => env('SUMSUB_INDIVIDUAL_LEVEL_1_NAME'),
                'individual_level_2' => env('SUMSUB_INDIVIDUAL_LEVEL_2_NAME'),
                'individual_level_3' => env('SUMSUB_INDIVIDUAL_LEVEL_3_NAME'),
                'corporate_level_1' => env('SUMSUB_CORPORATE_LEVEL_1_NAME'),
                'corporate_level_2' => env('SUMSUB_CORPORATE_LEVEL_2_NAME'),
                'corporate_level_3' => env('SUMSUB_CORPORATE_LEVEL_3_NAME'),
            ],
        ],
        'chainalysis'=>[
            'cratos' => [
                'api_url' => env('chainalysisis_api_url'),
                'app_token' => env('chainalysisis_api_key'),
            ],
        ]
    ],
    'serviceObject' => [
        'sum_sub' => '\App\Services\SumSubService'
    ]

];

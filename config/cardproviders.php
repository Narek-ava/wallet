<?php

return [
    'trustPayments' => [
        'cratos' => [
            'spaceId' => env('TRUST_PAYMENT_SPACE_ID'),
            'userId' => env('TRUST_PAYMENT_USER_ID'),
            'secret' => env('TRUST_PAYMENT_SECRET'),
            'username' => env('TRUST_PAYMENT_USERNAME'),
            'password' => env('TRUST_PAYMENT_PASSWORD'),
            'sitereference' => env('TRUST_PAYMENT_SITEREFERENCE')
        ],

    ],

//    'paydo' => [
//        'connectee' => [
//            'spaceId' => env('TRUST_PAYMENT_SPACE_ID'),
//            'userId' => env('TRUST_PAYMENT_USER_ID'),
//            'secret' => env('TRUST_PAYMENT_SECRET'),
//            'username' => env('TRUST_PAYMENT_USERNAME'),
//            'password' => env('TRUST_PAYMENT_PASSWORD'),
//            'sitereference' => env('TRUST_PAYMENT_SITEREFERENCE')
//        ],
//    ]
];

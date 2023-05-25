<?php

namespace App\Enums;

class PaymentFormTypes extends Enum
{
    const TYPE_MERCHANT_OUTSIDE_FORM = 1;
    const TYPE_MERCHANT_INSIDE_FORM = 2;
    const TYPE_CLIENT_OUTSIDE_FORM = 3;
    const TYPE_CLIENT_INSIDE_FORM = 4;
    const TYPE_CRYPTO_TO_CRYPTO_FORM = 5;

    const NAMES = [
        self::TYPE_MERCHANT_OUTSIDE_FORM => 'enum_type_merchant_outside_form',
        self::TYPE_MERCHANT_INSIDE_FORM => 'enum_type_merchant_inside_form',
        self::TYPE_CLIENT_OUTSIDE_FORM => 'enum_type_client_outside_form',
        self::TYPE_CLIENT_INSIDE_FORM => 'enum_type_client_inside_form',
        self::TYPE_CRYPTO_TO_CRYPTO_FORM => 'enum_type_crypto_to_crypto_form',
    ];

    const AVAILABLE_FORM_TYPES = [
        self::TYPE_MERCHANT_OUTSIDE_FORM,
        self::TYPE_MERCHANT_INSIDE_FORM,
        self::TYPE_CLIENT_OUTSIDE_FORM,
        self::TYPE_CLIENT_INSIDE_FORM,
    ];

    const PAYMENT_INSIDE_FORMS = [
        self::TYPE_MERCHANT_INSIDE_FORM,
        self::TYPE_CLIENT_INSIDE_FORM
    ];

    const PAYMENT_OUTSIDE_FORMS = [
        self::TYPE_MERCHANT_OUTSIDE_FORM,
        self::TYPE_CLIENT_OUTSIDE_FORM
    ];

    const CLIENT_PAYMENT_FORMS = [
        self::TYPE_CLIENT_OUTSIDE_FORM,
        self::TYPE_CLIENT_INSIDE_FORM
    ];

    const MERCHANT_PAYMENT_FORMS = [
        self::TYPE_MERCHANT_OUTSIDE_FORM,
        self::TYPE_MERCHANT_INSIDE_FORM
    ];
}

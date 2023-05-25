<?php
namespace App\Enums;

/**
 * PaymentSystemTypes
 * @todo rename
 *
 */
class PaymentSystemTypes extends Enum
{
    const SYSTEM_MASTERCARD = 1;
    const SYSTEM_VISA= 2;

    const NAMES = [
        self::SYSTEM_MASTERCARD => 'ui_mastercard',
        self::SYSTEM_VISA => 'ui_visa',
    ];

    const TYPES_3DS = [
        self::SYSTEM_MASTERCARD,
        self::SYSTEM_VISA,
    ];
}

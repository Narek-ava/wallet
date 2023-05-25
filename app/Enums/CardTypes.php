<?php


namespace App\Enums;


class CardTypes
{
    const TYPE_VISA = 1;
    const TYPE_MASTERCARD = 2;
    const TYPE_VISA_KEY = 'VISA';
    const TYPE_MASTERCARD_KEY = 'MASTERCARD';

    const TYPES = [
        self::TYPE_VISA_KEY => self::TYPE_VISA,
        self::TYPE_MASTERCARD_KEY => self::TYPE_MASTERCARD,
    ];
}

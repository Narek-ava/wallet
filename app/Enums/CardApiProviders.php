<?php


namespace App\Enums;


class CardApiProviders extends Enum
{
    const TRUSTPAYMENTS = 'trustPayments';
    const PAYDO = 'paydo';


    const CARD_API_PROVIDERS = [
        self::TRUSTPAYMENTS,
        self::PAYDO
    ];
}

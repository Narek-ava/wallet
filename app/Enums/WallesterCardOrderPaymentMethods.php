<?php


namespace App\Enums;


class WallesterCardOrderPaymentMethods extends Enum
{
    const BANK_CARD = 1;
    const SEPA = 2;
    const CRYPTOCURRENCY = 3;

    const NAMES = [
        self::BANK_CARD => 'ui_bank_card_transfer',
        self::SEPA => 'sepa',
        self::CRYPTOCURRENCY => 'top_up_deposit_cryptocurrency'
    ];

}

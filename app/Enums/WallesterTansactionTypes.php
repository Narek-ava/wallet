<?php


namespace App\Enums;


class WallesterTansactionTypes extends Enum
{
    const TYPE_DEPOSIT = 'Deposit';
    const TYPE_WITHDRAW = 'Withdraw';
    const TYPE_INTERNET_PURCHASE = 'InternetPurchase';
    const TYPE_PURCHASE = 'Purchase';
    const TYPE_OTHER = 'Other';

    const TYPES = [
        self::TYPE_DEPOSIT,
        self::TYPE_WITHDRAW,
        self::TYPE_INTERNET_PURCHASE,
        self::TYPE_PURCHASE,
        self::TYPE_OTHER,
    ];

    const NAMES = [
        self::TYPE_DEPOSIT => 'enum_type_deposit',
        self::TYPE_WITHDRAW => 'enum_type_withdraw',
        self::TYPE_INTERNET_PURCHASE => 'enum_type_internet_purchase',
        self::TYPE_PURCHASE => 'enum_type_purchase',
        self::TYPE_OTHER => 'enum_type_other',
    ];
}

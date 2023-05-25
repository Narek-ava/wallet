<?php


namespace App\Enums;


class ExchangeRequestType extends Enum
{
    const EXCHANGE_TYPE_WIRE = 0;
    const EXCHANGE_TYPE_CC = 1;
    const EXCHANGE_TYPE_CRYPTO = 2;


    const NAMES = [
        self::EXCHANGE_TYPE_WIRE => 'enum_exchange_type_wire',
        self::EXCHANGE_TYPE_CC => 'enum_exchange_type_cc',
        self::EXCHANGE_TYPE_CRYPTO => 'enum_exchange_type_crypto',
    ];


}

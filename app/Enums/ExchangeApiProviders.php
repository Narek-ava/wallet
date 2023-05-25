<?php


namespace App\Enums;


class ExchangeApiProviders extends Enum
{
    const EXCHANGE_KRAKEN = 'kraken';
    const EXCHANGE_BITSTAMP = 'bitstamp';

    const NAMES = [
        self::EXCHANGE_KRAKEN => 'exchange_api_name_kraken',
        self::EXCHANGE_BITSTAMP => 'exchange_api_name_bitstamp',
    ];
}

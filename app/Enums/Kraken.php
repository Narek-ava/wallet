<?php


namespace App\Enums;


class Kraken extends Enum
{
    const KRAKEN_ADD_ORDER_TYPE_BUY = 'buy';
    const KRAKEN_ADD_ORDER_TYPE_SELL = 'sell';

    const KRAKEN_ADD_ORDER_ORDER_TYPE_MARKET = 'market';
    const KRAKEN_ADD_ORDER_ORDER_TYPE_LIMIT = 'limit';
    const KRAKEN_ADD_ORDER_ORDER_TYPE_STOP_LOSS = 'stop-loss';
    const KRAKEN_ADD_ORDER_ORDER_TYPE_TAKE_PROFIT = 'take-profit';
    const KRAKEN_ADD_ORDER_ORDER_TYPE_STOP_LOSS_LIMIT = 'stop-loss-limit';
    const KRAKEN_ADD_ORDER_ORDER_TYPE_TAKE_PROFIT_LIMIT = 'take-profit-limit';
    const KRAKEN_ADD_ORDER_ORDER_TYPE_SETTLE_POSITION = 'settle-position';
}

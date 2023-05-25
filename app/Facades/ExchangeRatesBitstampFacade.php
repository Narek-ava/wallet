<?php


namespace App\Facades;

use App\Services\ExchangeRatesBitstampService;
use \Illuminate\Support\Facades\Facade;

/**
 * @method static float|int|string[] rate(float $amount = 1, string $from = 'usd', string $to = 'eur')
 *
 * @see ExchangeRatesBitstampService
 */
class ExchangeRatesBitstampFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ExchangeRatesBitstampFacade';
    }
}


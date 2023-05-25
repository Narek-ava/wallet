<?php
namespace App\Enums;

use App\Services\WalletService;

class SumsubCrypto extends Enum
{
    const SUMSUB_BTC = 0;
    const SUMSUB_LTC = 1;
//    const SUMSUB_ETH = 2;
    const SUMSUB_USDT = 3;
    const SUMSUB_ERC_20 = 4;
    const SUMSUB_BCH = 7;

    const NAMES = [
        self::SUMSUB_BTC => 'BTC',
        self::SUMSUB_LTC => 'LTC',
//        self::SUMSUB_ETH => 'ETH',
        self::SUMSUB_USDT => 'USDT',
        self::SUMSUB_ERC_20 => 'ERC-20',
        self::SUMSUB_BCH => 'BCH',
    ];

    public static function getBitGoNames(string $projectId)
    {
        $coins = [];
        /* @var WalletService $walletService */
        $walletService = resolve(WalletService::class);

        foreach (self::NAMES as $key => $name){
           $coins[$name] = $walletService->getConfigs($projectId,'coin_prefix') . strtolower($name);
        }
        return $coins;
    }

    public static function changeToBitgoName($coin, string $projectId)
    {
        /* @var WalletService $walletService */
        $walletService = resolve(WalletService::class);
        $bitgoCoin = $walletService->getConfigs($projectId,'coin_prefix') . strtolower($coin) ;

        return $bitgoCoin;
    }



}

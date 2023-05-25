<?php


namespace App\Enums;


use App\Services\WalletService;

class Currency extends Enum
{

    /**
     * get constant lists with translated names
     * @param string|null $lang
     * @return array
     */
    public static function getList($lang = null)
    {
        return array_intersect(static::ADDITIONAL_CURRENCY_NAMES, config('cratos.enabled_coins'));
    }

    /**
     * get name translation
     * @param int|string $key
     * @param string|null $lang
     * @return array|string|null
     */
    public static function getName($key, $lang = null)
    {
        $names = static::getList();

        return $names[$key] ?? null;
    }

    public static function getAllCurrencies()
    {
        return array_merge(self::FIAT_CURRENCY_NAMES, self::getList());
    }

    const CURRENCY_USD = 'USD';
    const CURRENCY_EUR= 'EUR';
    const CURRENCY_AUD= 'AUD';
    const CURRENCY_CAD= 'CAD';
    const CURRENCY_BTC = 'BTC';
    const CURRENCY_LTC = 'LTC';
    const CURRENCY_BCH= 'BCH';
    const CURRENCY_GBP= 'GBP';
    const CURRENCY_ETH= 'ETH';
    const CURRENCY_XRP= 'XRP';
    const CURRENCY_USDT= 'USDT';
    const CURRENCY_USDC= 'USDC';
    const CURRENCY_WBTC= 'WBTC';
    const CURRENCY_LN= 'LINK';
    const CURRENCY_TRX= 'TRX';
    const CURRENCY_UNI= 'UNI';
    const CURRENCY_DASH= 'DASH';
    const CURRENCY_ZEC= 'ZEC';
    const CURRENCY_MCDAI= 'MCDAI';


    const TX_EXPLORER_LTC = 'https://live.blockcypher.com/ltc/tx/{tx_id}';
    const TX_EXPLORER_BTC = 'https://mempool.space/tx/{tx_id}';
    const TX_EXPLORER_BCH = 'https://blockexplorer.one/bitcoin-cash/mainnet/tx/{tx_id}';
    const TX_EXPLORER_XRP = 'https://xrpscan.com/tx/{tx_id}';
    const TX_EXPLORER_ETH = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_USDT = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_USDC = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_WBTC = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_DASH = 'https://explorer.dash.org/insight/tx/{tx_id}';
    const TX_EXPLORER_LN = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_UNI = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_DAI = 'https://etherscan.io/tx/{tx_id}';
    const TX_EXPLORER_TRX = 'https://tronscan.org/#/transaction/{tx_id}';
    const TX_EXPLORER_ZEC = 'https://zecblockexplorer.com/tx/{tx_id}';

    const TX_EXPLORER_MAP = [
        self::CURRENCY_BTC => self::TX_EXPLORER_BTC,
        self::CURRENCY_LTC => self::TX_EXPLORER_LTC,
        self::CURRENCY_BCH => self::TX_EXPLORER_BCH,
        self::CURRENCY_XRP => self::TX_EXPLORER_XRP,
        self::CURRENCY_ETH => self::TX_EXPLORER_ETH,
        self::CURRENCY_USDT => self::TX_EXPLORER_USDT,
        self::CURRENCY_USDC => self::TX_EXPLORER_USDC,
        self::CURRENCY_DASH => self::TX_EXPLORER_DASH,
        self::CURRENCY_WBTC => self::TX_EXPLORER_WBTC,
        self::CURRENCY_LN => self::TX_EXPLORER_LN,
        self::CURRENCY_UNI => self::TX_EXPLORER_UNI,
        self::CURRENCY_MCDAI => self::TX_EXPLORER_DAI,
        self::CURRENCY_TRX => self::TX_EXPLORER_TRX,
        self::CURRENCY_ZEC => self::TX_EXPLORER_ZEC,
    ];

    const IMAGES = [
        self::CURRENCY_BTC => 'btc.png',
        self::CURRENCY_LTC => 'ltc.png',
        self::CURRENCY_BCH => 'bch.png',
        self::CURRENCY_ETH => 'eth.png',
        self::CURRENCY_XRP => 'xrp.png',
        self::CURRENCY_USDT => 'usdt.png',
        self::CURRENCY_USDC => 'usdc.png',
        self::CURRENCY_WBTC => 'wbtc.png',
        self::CURRENCY_LN => 'ln.png',
        self::CURRENCY_TRX => 'trx.png',
        self::CURRENCY_UNI => 'uni.png',
        self::CURRENCY_DASH => 'dash.png',
        self::CURRENCY_ZEC => 'zec.png',
        self::CURRENCY_MCDAI => 'dai.png',
        self::CURRENCY_USD => 'usd.png',
        self::CURRENCY_EUR => 'eur.png',
        self::CURRENCY_CAD => 'cad.png',
        self::CURRENCY_GBP => 'gbp.png',
        self::CURRENCY_AUD => 'aud.png',
    ];


    const ALL_NAMES = self::FIAT_CURRENCY_NAMES + self::NAMES;

    const ADDITIONAL_CURRENCY_NAMES = [
        self::CURRENCY_BTC => 'BTC',
        self::CURRENCY_LTC => 'LTC',
        self::CURRENCY_BCH => 'BCH',
        self::CURRENCY_ETH => 'ETH',
        self::CURRENCY_XRP => 'XRP',
        self::CURRENCY_USDT => 'USDT',
        self::CURRENCY_USDC => 'USDC',
        self::CURRENCY_WBTC => 'WBTC',
        self::CURRENCY_LN => 'LINK',
        self::CURRENCY_TRX => 'TRX',
        self::CURRENCY_UNI => 'UNI',
        self::CURRENCY_DASH => 'DASH',
        self::CURRENCY_ZEC => 'ZEC',
        self::CURRENCY_MCDAI => 'MCDAI',
    ];

    const NAMES = [
        self::CURRENCY_BTC => 'BTC',
        self::CURRENCY_LTC => 'LTC',
        self::CURRENCY_BCH => 'BCH',
    ];

    const FIAT_CURRENCY_NAMES = [
        self::CURRENCY_USD => self::CURRENCY_USD,
        self::CURRENCY_EUR => self::CURRENCY_EUR,
        self::CURRENCY_GBP => self::CURRENCY_GBP,
        self::CURRENCY_AUD => self::CURRENCY_AUD,
        self::CURRENCY_CAD => self::CURRENCY_CAD,
    ];

    const FIAT_CURRENCY_SYMBOLS = [
        self::CURRENCY_USD => '$',
        self::CURRENCY_EUR => '€',
        self::CURRENCY_GBP => '£',
        self::CURRENCY_AUD => 'A$',
        self::CURRENCY_CAD => 'Can$',
    ];

    public static function getBitGoAllowedCurrencies(string $projectId){
        /* @var WalletService $walletService */
        $walletService = resolve(WalletService::class);

        $allowedCurrencies = [];
        foreach (static::getList() as $currency){
            $allowedCurrencies[ $walletService->getConfigs($projectId,'coin_prefix') . strtolower($currency)] = $currency;
        }
        return $allowedCurrencies;
    }

    public static function getDefaultWalletCoin($projectId){
        /* @var WalletService $walletService */
        $walletService = resolve(WalletService::class);
        return $walletService->getConfigs($projectId,'coin_prefix') . strtolower( static::CURRENCY_BTC);
    }

    const FULL_NAMES = [
        self::CURRENCY_BTC => 'Bitcoin',
        self::CURRENCY_LTC => 'Litecoin',
        self::CURRENCY_BCH => 'Bitcoin Cash',
        self::CURRENCY_ETH => 'Ethereum',
        self::CURRENCY_XRP => 'Ripple',
        self::CURRENCY_USDT => 'Tether',
        self::CURRENCY_USDC => 'USD Coin',
        self::CURRENCY_WBTC => 'Wrapped Bitcoin',
        self::CURRENCY_LN => 'Chainlink',
        self::CURRENCY_TRX => 'Tron',
        self::CURRENCY_UNI => 'Uniswap',
        self::CURRENCY_DASH => 'Dash',
        self::CURRENCY_ZEC => 'Zcash',
        self::CURRENCY_MCDAI => 'Dai',
    ];

    const BASE_CURRENCY = [
        self::CURRENCY_BTC => 100000000, //Satoshi
        self::CURRENCY_LTC => 100000000, //Litoshi
        self::CURRENCY_BCH => 100000000, //Satoshi
        self::CURRENCY_ETH => 1000000000000000000,
        self::CURRENCY_XRP => 1000000,
        self::CURRENCY_USDT => 1000000,
        self::CURRENCY_USDC => 1000000,
        self::CURRENCY_WBTC => 100000000,
        self::CURRENCY_LN => 1000000000000000000,
        self::CURRENCY_TRX => 1000000,
        self::CURRENCY_UNI => 1000000000000000000,
        self::CURRENCY_DASH => 100000000,
        self::CURRENCY_ZEC => 100000000,
        self::CURRENCY_MCDAI => 1000000000000000000,
    ];

    const NEW_FIAT_CURRENCIES = [
        self::CURRENCY_USD => self::CURRENCY_USD,
        self::CURRENCY_EUR => self::CURRENCY_EUR,
        self::CURRENCY_GBP => self::CURRENCY_GBP,
    ];


    const TOKENS_WITH_SUBTOKENS = [
        self::CURRENCY_ETH => [
            self::CURRENCY_MCDAI,
            self::CURRENCY_USDT,
            self::CURRENCY_USDC,
            self::CURRENCY_WBTC,
            self::CURRENCY_UNI,
            self::CURRENCY_LN,
        ],
    ];

    const MAIN_CRYPTOCURRENCIES = [
        self::CURRENCY_BTC,
        self::CURRENCY_ETH,
        self::CURRENCY_BCH,
        self::CURRENCY_LTC,
    ];

    const CURRENCIES_NEAR_USD = [
        self::CURRENCY_USDT,
        self::CURRENCY_USDC,
        self::CURRENCY_UNI,
        self::CURRENCY_MCDAI,
        self::CURRENCY_LN
    ];

    const KRAKEN_ASSETS = [
        self::CURRENCY_MCDAI => 'DAI',
    ];

}

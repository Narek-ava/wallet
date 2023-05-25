<?php
namespace App\Enums;

class Providers extends Enum
{
    const PROVIDER_PAYMENT = 1;
    const PROVIDER_LIQUIDITY = 2;
    const PROVIDER_WALLET = 3;
    const PROVIDER_CARD = 4;
    const CLIENT = 5;
    const PROVIDER_CARD_ISSUING = 6;
    const PROVIDER_COMPLIANCE = 7;

    const NAMES = [
        self::PROVIDER_PAYMENT => 'Payment provider',
        self::PROVIDER_LIQUIDITY => 'Liquidity provider',
        self::PROVIDER_WALLET => 'Wallet provider',
        self::PROVIDER_CARD => 'Credit card provider',
        self::CLIENT => 'Client',
        self::PROVIDER_COMPLIANCE => 'Compliance provider',
        self::PROVIDER_CARD_ISSUING => 'Card issuing provider',
    ];

    const ONLY_PROVIDER_NAMES = [
        self::PROVIDER_PAYMENT => self::NAMES[self::PROVIDER_PAYMENT],
        self::PROVIDER_LIQUIDITY => self::NAMES[self::PROVIDER_LIQUIDITY],
        self::PROVIDER_WALLET => self::NAMES[self::PROVIDER_WALLET],
        self::PROVIDER_CARD => self::NAMES[self::PROVIDER_CARD],
        self::PROVIDER_COMPLIANCE => self::NAMES[self::PROVIDER_COMPLIANCE],
        self::PROVIDER_CARD_ISSUING => self::NAMES[self::PROVIDER_CARD_ISSUING],
    ];
}

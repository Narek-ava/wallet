<?php

namespace App\Enums;

class CollectedCryptoFee extends Enum
{
    const IS_NOT_COLLECTED = 0;
    const IS_COLLECTED = 1;

    const NAMES = [
        self::IS_NOT_COLLECTED => 'enum_collected_crypto_fee_not_collected',
        self::IS_COLLECTED => 'enum_collected_crypto_fee_collected'
    ];

}

<?php
namespace App\Enums;

/**
 * CardProviderRegions
 * @todo rename
 *
 */
class CardProviderRegions extends Enum
{
    const CARD_REGION_EEA = 1;
    const CARD_REGION_INTERNATIONAL = 2;


    const NAMES = [
        self::CARD_REGION_EEA => 'ui_region_eea',
        self::CARD_REGION_INTERNATIONAL => 'ui_region_international',
    ];

    const TYPES = [
        self::CARD_REGION_EEA,
        self::CARD_REGION_INTERNATIONAL,
    ];
}

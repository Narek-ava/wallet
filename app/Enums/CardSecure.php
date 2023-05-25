<?php
namespace App\Enums;

/**
 * CardSecure
 * @todo rename
 *
 */
class CardSecure extends Enum
{
    const CARD_SECURE = 1;
    const CARD_NON_SECURE = 2;


    const NAMES = [
        self::CARD_SECURE => 'ui_card_secure',
        self::CARD_NON_SECURE => 'ui_card_non_secure',
    ];

    const TYPES = [
        self::CARD_SECURE,
        self::CARD_NON_SECURE,
    ];
}

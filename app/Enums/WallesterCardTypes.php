<?php


namespace App\Enums;


class WallesterCardTypes extends Enum
{
    const TYPE_PLASTIC = 1;
    const TYPE_VIRTUAL = 2;


    const NAMES = [
        self::TYPE_PLASTIC => 'card_type_plastic',
        self::TYPE_VIRTUAL => 'card_type_virtual'
    ];

    const CARD_TYPES_LOWER = [
        self::TYPE_PLASTIC => 'plastic',
        self::TYPE_VIRTUAL => 'virtual'
    ];
}

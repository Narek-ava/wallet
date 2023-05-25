<?php


namespace App\Enums;


class ComplianceLevel extends Enum
{
    // @note я бы убрал префикс VERIFICATION_
    const VERIFICATION_NOT_VERIFIED = -1;
    const VERIFICATION_LEVEL_0 = 0;
    const VERIFICATION_LEVEL_1 = 1;
    const VERIFICATION_LEVEL_2 = 2;
    const VERIFICATION_LEVEL_3 = 3;

    const NAMES = [
        self::VERIFICATION_LEVEL_0 => 'enum_compliance_level_level_0',
        self::VERIFICATION_LEVEL_1 => 'enum_compliance_level_level_1',
        self::VERIFICATION_LEVEL_2 => 'enum_compliance_level_level_2',
        self::VERIFICATION_LEVEL_3 => 'enum_compliance_level_level_3',
    ];

    const AVAILABLE_LEVELS = [
        self::VERIFICATION_LEVEL_1,
        self::VERIFICATION_LEVEL_2,
        self::VERIFICATION_LEVEL_3
    ];

}

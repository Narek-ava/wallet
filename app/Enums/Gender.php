<?php


namespace App\Enums;


class Gender extends Enum
{

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    const SUMSUB_GENDER_MALE = 'M';
    const SUMSUB_GENDER_FEMALE = 'F';

    const TYPE_GENDER = [
        self::SUMSUB_GENDER_MALE => self::GENDER_MALE,
        self::SUMSUB_GENDER_FEMALE => self::GENDER_FEMALE,
    ];

    const NAMES = [
        self::GENDER_MALE => 'enum_gender_male',
        self::GENDER_FEMALE => 'enum_gender_female',
    ];
}

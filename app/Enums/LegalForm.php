<?php


namespace App\Enums;


class LegalForm extends Enum
{
    //legal form types constants
    const LF_COMPANY = 1;
    const LF_CONGLOMERATE = 2;
    const LF_COOPERATIVE = 3;
    const LF_HOLDING_COMPANY = 4;
    const LF_JOINT_STOCK = 5;
    const LF_DLC = 6;
    const LF_LLC = 7;
    const LF_LLP = 8;
    const LF_LP = 9;
    const LF_PARTNERSHIP = 10;
    const LF_SOLO_PROPRIETORSHIP = 11;



    const NAMES = [
        self::LF_COMPANY => 'enum_lf_company',
        self::LF_CONGLOMERATE => 'enum_lf_conglomerate',
        self::LF_COOPERATIVE => 'enum_lf_cooperative',
        self::LF_HOLDING_COMPANY => 'enum_lf_holding_company',
        self::LF_JOINT_STOCK => 'enum_lf_joint_stock',
        self::LF_DLC => 'enum_lf_limited_duration_company',
        self::LF_LLC => 'enum_lf_limited_liability_company',
        self::LF_LLP => 'enum_lf_limited_liability_partnership',
        self::LF_LP => 'enum_lf_limited_partnership',
        self::LF_PARTNERSHIP => 'enum_lf_partnership',
        self::LF_SOLO_PROPRIETORSHIP => 'enum_lf_solo_proprietorship',
    ];


}

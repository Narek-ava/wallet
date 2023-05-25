<?php

namespace App\Enums;

class TwoFAType extends Enum
{
    const NONE = 0;
    const GOOGLE = 1;
    const EMAIL  = 2;
    const SMS = 3;
    const TELEGRAM = 4;

    const TWO_FA_CONNECTED = 'two_fa_connected';
    const TWO_FA_NOT_CONNECTED = 'two_fa_not_connected';

    const NAMES = [
        self::NONE => 'enum_2fa_none',
        self::EMAIL => 'enum_2fa_email',
        self::GOOGLE => 'enum_2fa_google',
        self::SMS => 'enum_2fa_sms',
        self::TELEGRAM => 'enum_2fa_telegram',
    ];
}

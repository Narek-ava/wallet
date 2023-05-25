<?php


namespace App\Enums;


use DateTimeZone;

class TimezoneEnum extends Enum
{
    const TIMEZONE_DEFAULT = 'UTC';

    public static function getAllTimezones(): array
    {
        return DateTimeZone::listIdentifiers();
    }
}

<?php


namespace App\Enums;


class RateTemplatesStatuses extends Enum
{
    const RATE_TEMPLATE_NOT_DEFAULT = 0;
    const RATE_TEMPLATE_DEFAULT = 1;
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    const STATUSES = [
        self::STATUS_ACTIVE => 'ACTIVE',
        self::STATUS_DISABLED => 'DISABLED',
    ];
}

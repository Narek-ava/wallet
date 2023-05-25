<?php


namespace App\Enums;


class ProjectStatuses extends Enum
{

    const STATUS_DISABLED = 1;
    const STATUS_ACTIVE = 2;

    const NAMES = [
        self::STATUS_DISABLED => 'enum_status_disabled',
        self::STATUS_ACTIVE => 'enum_status_active'
    ];
}

<?php


namespace App\Enums;


class NotificationStatuses extends Enum
{
    const NOT_VIEWED = 0;
    const VIEWED = 1;

    const NAMES = [
        self::NOT_VIEWED => 'enum_notification_not_seen',
        self::VIEWED => 'enum_notification_seen',
    ];
}

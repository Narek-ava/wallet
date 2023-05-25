<?php


namespace App\Enums;


class LogLevel extends Enum
{
    const LEVEL_EMERGENCY = 1;
    const LEVEL_ALERT = 2;
    const LEVEL_CRITICAL = 3;
    const LEVEL_ERROR = 4;
    const LEVEL_WARNING = 5;
    const LEVEL_NOTICE = 6;
    const LEVEL_INFO = 7;
    const LEVEL_DEBUG = 8;

    const NAMES = [
        self::LEVEL_EMERGENCY => 'enum_log_level_emergency',
        self::LEVEL_ALERT => 'enum_log_level_alert',
        self::LEVEL_CRITICAL => 'enum_log_level_critical',
        self::LEVEL_ERROR => 'enum_log_level_error',
        self::LEVEL_WARNING => 'enum_log_level_warning',
        self::LEVEL_NOTICE => 'enum_log_level_notice',
        self::LEVEL_INFO => 'enum_log_level_info',
        self::LEVEL_DEBUG => 'enum_log_level_debug',
    ];

    const LEVEL_BY_NAMES = [
        'emergency' => self::LEVEL_EMERGENCY,
        'alert' =>  self::LEVEL_ALERT,
        'critical' => self::LEVEL_CRITICAL,
        'error' => self::LEVEL_ERROR,
        'warning' => self::LEVEL_WARNING,
        'notice' => self::LEVEL_NOTICE,
        'info' => self::LEVEL_INFO,
        'debug' =>  self::LEVEL_DEBUG
    ];
}

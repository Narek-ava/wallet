<?php


namespace App\Enums;


class LogResult extends Enum
{
    const RESULT_SUCCESS = 1;
    const RESULT_NEUTRAL = 2;
    const RESULT_FAILURE = 3;

    const NAMES = [
        self::RESULT_SUCCESS => 'enum_log_result_success',
        self::RESULT_NEUTRAL => 'enum_log_result_neutral',
        self::RESULT_FAILURE => 'enum_log_result_failure',
    ];
}

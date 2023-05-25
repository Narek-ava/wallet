<?php


namespace App\Enums;


class OperationStatuses extends Enum
{
    const PENDING = 0;
//    const REFUND = 2;
    const SUCCESSFUL = 1;
    const DECLINED = 4;
    const RETURNED = 2;

    const NAMES = [
        self::PENDING => 'PENDING',
//        self::REFUND => 'REFUND',
        self::SUCCESSFUL => 'SUCCESSFUL',
        self::DECLINED => 'DECLINED',
        self::RETURNED => 'RETURNED',
    ];

    const VALUES = [
        self::PENDING,
        self::SUCCESSFUL,
        self::DECLINED,
        self::RETURNED,
    ];
}

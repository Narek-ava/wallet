<?php


namespace App\Enums;


class TransactionStatuses extends Enum
{
    const PENDING = 0;
    const REFUND = 2;
    const SUCCESSFUL = 1;
    const DECLINED = 4;
    const RETURNED = 5;

    const NAMES = [
        self::PENDING => 'PENDING',
        self::REFUND => 'REFUND',
        self::SUCCESSFUL => 'SUCCESSFUL',
        self::DECLINED => 'DECLINED',
        self::RETURNED => 'RETURNED',
    ];
}

<?php

namespace App\Enums;

class OperationSubStatuses extends Enum
{
    const NONE = 0;
    const INSUFFICIENT_FUNDS = 1;
    const RUNTIME_ERROR = 2;
//    const DECLINED = 3;
    const CHARGEBACK = 4;
    const REFUND = 5;

    const NAMES = [
        self::INSUFFICIENT_FUNDS => 'enum_substatus_insufficient_funds',
        self::RUNTIME_ERROR => 'enum_substatus_runtime_error',
//        self::DECLINED => 'enum_substatus_declined',
        self::CHARGEBACK => 'enum_substatus_chargeback',
        self::REFUND => 'enum_substatus_refund',
    ];
}

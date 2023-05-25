<?php

namespace App\Enums;

class PaymentFormStatuses extends Enum
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;

    const NAMES = [
        self::STATUS_ACTIVE => 'enum_status_active',
        self::STATUS_DISABLED => 'enum_status_disabled',
    ];
}

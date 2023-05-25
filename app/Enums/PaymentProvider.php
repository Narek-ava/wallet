<?php
namespace App\Enums;

/**
 * PaymentProviderStatus
 * @todo rename
 *
 */
class PaymentProvider extends Enum
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_SUSPENDED = 3;

    
    const NAMES = [
        self::STATUS_ACTIVE => 'enum_status_active',
        self::STATUS_DISABLED => 'enum_status_disabled',
        self::STATUS_SUSPENDED => 'enum_status_suspended',
    ];
}

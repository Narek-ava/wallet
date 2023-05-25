<?php


namespace App\Enums;


class ExchangeRequestStatuses extends Enum
{
    const STATUS_WAITING_FOR_CLIENT_APPROVAL = 0;
    const STATUS_WAITING_FOR_DEPOSIT = 1;
    const STATUS_SUCCESSFUL = 2;
    const STATUS_DECLINED = 3;
    const STATUS_UPGRADE_VERIFICATION_LEVEL = 4;
    const STATUS_DELETED = 5;

    const NAMES = [
        self::STATUS_WAITING_FOR_CLIENT_APPROVAL => 'enum_status_waiting_for_client_approval',
        self::STATUS_WAITING_FOR_DEPOSIT => 'enum_status_waiting_for_deposit',
        self::STATUS_SUCCESSFUL => 'enum_status_successful',
        self::STATUS_DECLINED => 'enum_status_declined',
        self::STATUS_UPGRADE_VERIFICATION_LEVEL => 'enum_status_upgrade_verification_level',
        self::STATUS_DELETED => 'enum_status_deleted'
    ];


}

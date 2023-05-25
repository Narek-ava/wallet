<?php


namespace App\Enums;


class Commissions extends Enum
{
    const COMMISSION_ACTIVE = 1;
    const COMMISSION_INACTIVE = 0;

    const OUTGOING_COMMISSION = 1;

    const TYPE_INCOMING = 0;
    const TYPE_OUTGOING = 1;
    const TYPE_INTERNAL = 2;
    const TYPE_REFUND = 3;
    const TYPE_CHARGEBACK = 4;

    const NAMES = [
        self::TYPE_INCOMING => 'to_commission_id',
        self::TYPE_OUTGOING => 'from_commission_id',
        self::TYPE_INTERNAL => 'internal_commission_id',
        self::TYPE_REFUND => 'refund_commission_id',
        self::TYPE_CHARGEBACK => 'chargeback_commission_id',
    ];

    const NAMES_OF_COMMISSIONS = [
        self::TYPE_INCOMING => 'Incoming',
        self::TYPE_OUTGOING => 'Outgoing',
        self::TYPE_INTERNAL => 'Internal',
        self::TYPE_REFUND => 'Refund',
        self::TYPE_CHARGEBACK => 'Chargeback',
    ];

    const INCOMING_AND_OUTGOING_COMMISSIONS = [
        self::TYPE_INCOMING,
        self::TYPE_OUTGOING,
    ];
}

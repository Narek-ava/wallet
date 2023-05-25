<?php


namespace App\Enums;


class ComplianceRequest extends Enum
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DECLINED = 2;
    const STATUS_RETRY = 3;
    const STATUS_CANCELED = 4;

    const REVIEW_ANSWER_GREEN = 'GREEN';
    const REVIEW_ANSWER_RED = 'RED';


    const NAMES = [
        self::STATUS_PENDING => 'enum_compliance_request_status_pending',
        self::STATUS_APPROVED => 'enum_compliance_request_status_approved',
        self::STATUS_DECLINED => 'enum_compliance_request_status_declined',
        self::STATUS_RETRY => 'enum_compliance_request_status_retry',
        self::STATUS_CANCELED => 'enum_compliance_request_status_canceled',
    ];

    const SUM_SUB_STATUS_NAMES = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_APPROVED => 'completed',
    ];


}

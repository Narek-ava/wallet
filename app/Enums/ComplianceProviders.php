<?php


namespace App\Enums;


class ComplianceProviders extends Enum
{
    const SUMSUB = 'sum_sub';


    const NAMES = [
        self::SUMSUB => 'compliance_provider_sumsub',
    ];
}

<?php


namespace App\Enums;


class ReportTypes extends Enum
{
    const REPORT_OPERATIONS = 1;
    const REPORT_CLIENTS = 2;
    const REPORT_MERCHANT = 3;
    const REPORT_OPERATIONS_PDF = 4;

    const REPORT_TYPES = [
        self::REPORT_OPERATIONS => 'csv',
        self::REPORT_CLIENTS => 'csv',
        self::REPORT_MERCHANT => 'csv',
        self::REPORT_OPERATIONS_PDF => 'pdf',
    ];

}

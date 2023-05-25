<?php


namespace App\Enums;


class ReportStatuses extends Enum
{
    const REPORT_NEW = 1;
    const REPORT_PENDING = 2;
    const REPORT_COMPLETE = 3;
    const REPORT_DECLINE = 4;
}

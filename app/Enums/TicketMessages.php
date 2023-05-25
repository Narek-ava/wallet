<?php
namespace App\Enums;

use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CUser;

class TicketMessages extends Enum
{
    const VIEW_CUSER = 1;
    const VIEW_BUSER = 2;

    const NAMES = [
        self::VIEW_CUSER => CUser::class,
        self::VIEW_BUSER => BUser::class
    ];
}

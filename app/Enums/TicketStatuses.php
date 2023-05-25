<?php


namespace App\Enums;


class TicketStatuses extends Enum
{
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 2;
    const STATUS_NEW = 3;

    const NAMES = [
        self::STATUS_OPEN => 'ticket_opened',
        self::STATUS_CLOSE => 'ticket_closed',
        self::STATUS_NEW => 'ticket_new',
    ];
}

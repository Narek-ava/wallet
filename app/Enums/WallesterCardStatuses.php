<?php


namespace App\Enums;


class WallesterCardStatuses extends Enum
{
    const STATUS_PENDING_PAYMENT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_AWAITING_RENEWAL = 2;
    const STATUS_BLOCKED = 3;
    const STATUS_CLOSED = 4;
    const STATUS_CLOSING = 5;
    const STATUS_CREATED = 6;
    const STATUS_DISPATCHED = 7;
    const STATUS_EXPIRED = 8;
    const STATUS_ORDERED = 9;
    const STATUS_PERSONALIZED = 10;

    const NAMES = [
        self::STATUS_ACTIVE => 'wallester_card_status_active',
        self::STATUS_AWAITING_RENEWAL => 'wallester_card_status_awaiting_renewal',
        self::STATUS_BLOCKED => 'wallester_card_status_blocked',
        self::STATUS_CLOSED => 'wallester_card_status_closed',
        self::STATUS_CLOSING => 'wallester_card_status_closing',
        self::STATUS_CREATED => 'wallester_card_status_created',
        self::STATUS_DISPATCHED => 'wallester_card_status_dispatched',
        self::STATUS_EXPIRED => 'wallester_card_status_expired',
        self::STATUS_ORDERED => 'wallester_card_status_ordered',
        self::STATUS_PERSONALIZED => 'wallester_card_status_personalized',
        self::STATUS_PENDING_PAYMENT => "wallester_card_status_pending_payment"

    ];

    const STATUSES_FROM_RESPONSE = [
        "Active" => self::STATUS_ACTIVE,
        "AwaitingRenewal" => self::STATUS_AWAITING_RENEWAL,
        "Blocked" => self::STATUS_BLOCKED,
        "Closed" => self::STATUS_CLOSED,
        "Closing" => self::STATUS_CLOSING,
        "Created" => self::STATUS_CREATED,
        "Dispatched" => self::STATUS_DISPATCHED,
        "Expired" => self::STATUS_EXPIRED,
        "Ordered" => self::STATUS_ORDERED,
        "Personalized" => self::STATUS_PERSONALIZED
    ];

    const STATUSES_FOR_REQUESTS = [
        self::STATUS_ACTIVE => "Active",
        self::STATUS_AWAITING_RENEWAL => "AwaitingRenewal",
        self::STATUS_BLOCKED => "Blocked",
        self::STATUS_CLOSED => "Closed",
        self::STATUS_CLOSING => "Closing",
        self::STATUS_CREATED => "Created",
        self::STATUS_DISPATCHED => "Dispatched",
        self::STATUS_EXPIRED => "Expired",
        self::STATUS_ORDERED => "Ordered",
        self::STATUS_PERSONALIZED => "Personalized",
    ];
}

<?php


namespace App\Enums;


class NotificationRecipients extends Enum
{
    const ALL_CLIENTS = 1;
    const ALL_USERS = 2;
    const CURRENT_CLIENT = 3;
    const ALL_CORPORATE = 4;
    const ALL_INDIVIDUAL = 5;
    const MANAGER = 6;

    const RECIPIENTS = [
        self::ALL_CLIENTS => 'notification_recepient_all_clients',
        self::ALL_USERS => 'notification_recepient_all_users',
        self::CURRENT_CLIENT => 'notification_recepient_current_client',
        self::ALL_CORPORATE => 'notification_recepient_all_corporate',
        self::ALL_INDIVIDUAL => 'notification_recepient_all_individual',
    ];

    const MANAGERS = [
        self::MANAGER,
        self::ALL_USERS
    ];
}

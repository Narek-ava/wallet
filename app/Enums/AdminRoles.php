<?php

namespace App\Enums;

class AdminRoles extends Enum
{
    const IS_NOT_SUPER_ADMIN = 0;
    const IS_SUPER_ADMIN = 1;

    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    const NAMES = [
        self::IS_NOT_SUPER_ADMIN => 'enum_not_super_admin',
        self::IS_SUPER_ADMIN => 'enum_super_admin',
    ];

    const NAMES_STATUS = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DISABLED => 'Disabled',
    ];

}

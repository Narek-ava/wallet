<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ReferralPartner
 * @package App\Models
 * @property string $id
 * @property string $name
 * @property string $token
 * @property int $status
 * @property int $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ReferralPartner extends BaseModel
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    const STATUS_NAMES = [
        self::STATUS_ACTIVE => 'enum_status_active',
        self::STATUS_DISABLED => 'enum_status_disabled',
    ];

    protected $fillable = [
        'name', 'token', 'status', 'type',
    ];

}

<?php

namespace App\Models;


use App\Models\Cabinet\CProfile;
use Illuminate\Support\Carbon;

/**
 * @package App\Models
 * @property string $id
 * @property string $webhook_url
 * @property string $operation_id
 * @property string $merchant_id
 * @property int $attempts
 * @property int $status
 * @property string $error_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Operation $operation
 * @property CProfile $cProfile
 *
 */
class MerchantWebhookAttempt extends BaseModel
{
    const STATUS_PENDING = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    const MAX_ATTEMPTS = 5;

    protected $fillable = ['webhook_url', 'operation_id', 'merchant_id', 'attempts', 'status', 'created_at', 'updated_at'];

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }

    public function cProfile()
    {
        return $this->belongsTo(CProfile::class, 'merchant_id', 'id');
    }

}

<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * @package App\Models
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $phone_number
 * @property string $phone_cc
 * @property string $email
 * @property string $operation_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Operation $operation
 * @property string $full_name
 * @property string $formatted_phone_number
 *
 */
class MerchantOperationsInformation extends BaseModel
{
    protected $table = 'merchant_operations_information';

    protected $fillable = ['first_name', 'last_name', 'phone_number', 'phone_cc', 'email','operation_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operation()
    {
        return $this->belongsTo(Operation::class, 'id', 'operation_id');
    }

    /**
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return string
     */
    public function getFormattedPhoneNumberAttribute(): string
    {
        if (!($this->phone_cc && $this->phone_number)) {
            return '';
        }
        return '+(' . $this->phone_cc . ') ' . $this->phone_number;
    }
}

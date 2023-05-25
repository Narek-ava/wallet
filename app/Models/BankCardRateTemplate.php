<?php

namespace App\Models;

use App\Enums\{Commissions, CommissionType};
use App\Models\Cabinet\CProfile;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BankCardRateTemplate
 * @package App\Models
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $name
 * @property $status
 * @property $overview_type
 * @property $overview_fee
 * @property $transactions_type
 * @property $transactions_fee
 * @property $fees_type
 * @property $fees_fee
 * @property $project_id
 */
class BankCardRateTemplate extends BaseModel
{
    protected $fillable = [
        'name', 'status', 'overview_type', 'overview_fee', 'transactions_type', 'transactions_fee', 'fees_type', 'fees_fee', 'project_id'
    ];


}

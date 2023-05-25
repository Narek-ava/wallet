<?php

namespace App\Models;

use App\Enums\AccountStatuses;
use App\Enums\PaymentFormTypes;
use App\Models\Cabinet\CProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentFormAttempt
 * @property string $id
 * @property string $parameters
 * @property string $status
 * @property integer $format_type
 * @property string $report_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ReportRequestTemporary extends BaseModel
{
    protected $table = 'report_request_temporary';

    protected $fillable = ['parameters', 'status', 'format_type', 'report_type', 'created_at', 'updated_at'];

    public function getParamsAttribute()
    {
        if(!$this->parameters) {
            return [];
        }
        return json_decode($this->parameters, true);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ReferralPartner
 * @package App\Models
 * @property string $id
 * @property string $name
 * @property string $partner_id
 * @property string $individual_rate_templates_id
 * @property string $corporate_rate_templates_id
 * @property Carbon $activation_date
 * @property Carbon $deactivation_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property RateTemplate $individualRate
 * @property RateTemplate $corporateRate
 * @property ReferralPartner $partner
 */
class ReferralLink extends BaseModel
{

    protected $fillable = [
        'name', 'partner_id', 'individual_rate_templates_id', 'corporate_rate_templates_id', 'activation_date', 'deactivation_date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function individualRate()
    {
        return $this->belongsTo(RateTemplate::class, 'individual_rate_templates_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function corporateRate()
    {
        return $this->belongsTo(RateTemplate::class, 'corporate_rate_templates_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function partner()
    {
        return $this->belongsTo(ReferralPartner::class, 'partner_id', 'id');
    }

}

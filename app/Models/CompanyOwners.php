<?php

namespace App\Models;

use App\Models\Cabinet\CProfile;

/**
 * Class CompanyOwners
 * @package App\Models
 * @property string $id
 * @property string $c_profile_id
 * @property string $name
 * @property int $type
 * @property string $created_at
 * @property string $updated_at
 * @property CProfile $profile
 */
class CompanyOwners extends BaseModel
{
    protected $fillable = ['c_profile_id', 'name', 'type'];

    public function cProfile()
    {
        return $this->belongsTo(CProfile::class, 'c_profile_id', 'id');
    }

}

<?php

namespace App\Models;

use App\Models\Cabinet\CUser;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TwoFACode
 * @package App\Models
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $type
 * @property $value
 * @property $attempts
 * @property $status
 * @property $token
 * @property $expires_at
 * @property $c_user_id
 * @property CUser $cUser
 */
class TwoFACode extends BaseModel
{
    protected $table = '2fa_codes';
    protected $guarded = []; //? temporary
    protected $dates = ['expires_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(CUser::class, 'id', 'c_user_id');
    }
}

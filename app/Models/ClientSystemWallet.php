<?php

namespace App\Models;

/**
 * Class ClientSystemWallet
 * @package App\Models
 * @property $id
 * @property $wallet_id
 * @property $project_id
 * @property $passphrase
 * @property $currency
 * @property $created_at
 * @property $updated_at
 */
class ClientSystemWallet extends BaseModel
{

    protected $fillable = [
        'wallet_id', 'passphrase', 'currency', 'project_id'
    ];


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}

<?php


namespace App\Models\Backoffice;

/**
 * Class BUserProject
 * @package App\Models
 * @property $id
 * @property $b_user_id
 * @property $project_id
 * @property $created_at
 * @property $updated_at
 */
class BUserProject extends \App\Models\BaseModel
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];


}

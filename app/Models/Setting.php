<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 * @package App\Models
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $key
 * @property $project_id
 * @property $content
 * @property Project $project
 * @property $project_company_details
 */
class Setting extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
    protected $guarded = [];


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}

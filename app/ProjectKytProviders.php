<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectKytProviders extends Model
{
    protected $fillable = [
        'project_id' ,
        'kyt_provider_id'
    ];
}

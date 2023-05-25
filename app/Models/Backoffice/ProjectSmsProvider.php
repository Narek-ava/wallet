<?php


namespace App\Models\Backoffice;


use Illuminate\Database\Eloquent\Model;

class ProjectSmsProvider extends Model
{

    protected $fillable = [
        'project_id', 'key'
    ];

}

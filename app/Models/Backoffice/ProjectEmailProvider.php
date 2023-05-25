<?php


namespace App\Models\Backoffice;


use Illuminate\Database\Eloquent\Model;

class ProjectEmailProvider extends Model
{

    protected $fillable = [
        'project_id', 'key'
    ];

}

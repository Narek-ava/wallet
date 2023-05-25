<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KytProviders extends Model
{
    protected $fillable = ['name', 'api', 'api_account', 'status'];

//    protected $casts = [
//        'id' => 'string'
//    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_kyt_providers', 'kyt_provider_id', 'project_id');
    }

    public function scopeQueryByProject($query, ?string $projectId = null)
    {
        if ($projectId) {
            $query->whereHas('projects', function ($q) use ($projectId) {
                return $q->where('projects.id', $projectId);
            });
        }
        return $query;
    }
}

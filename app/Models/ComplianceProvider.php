<?php

namespace App\Models;

use App\{Enums\AccountStatuses,
    Enums\AccountType,
    Enums\OperationOperationType,
    Enums\Providers,
    Models\Backoffice\BUser};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ComplianceProvider
 * @package App\Models
 * @property $id
 * @property $name
 * @property $api
 * @property $api_account
 * @property $status
 * @property $created_at
 * @property $updated_at
 * @property Project[] $projects
 */
class ComplianceProvider extends BaseModel
{
    protected $fillable = ['id', 'name', 'api', 'api_account', 'status'];

    protected $casts = [
        'id' => 'string'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_compliance_providers', 'compliance_provider_id', 'project_id');
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

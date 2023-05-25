<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ApiClient
 * @package App\Models
 * @property string $id
 * @property string $name
 * @property string $key
 * @property string $token
 * @property int $status
 * @property string $project_id
 * @property int $access_token_expires_time
 * @property int $refresh_token_expires_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Project $project
 */
class ApiClient extends BaseModel
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 1;

    const STATUS_NAMES = [
        self::STATUS_ACTIVE => 'api_clients_statuses_active',
        self::STATUS_DISABLED => 'api_clients_statuses_disabled',
    ];

    protected $fillable = [
        'name', 'key', 'token', 'status', 'access_token_expires_time', 'refresh_token_expires_time', 'project_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

}

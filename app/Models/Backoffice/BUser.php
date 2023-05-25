<?php

namespace App\Models\Backoffice;

use App\Enums\AdminRoles;
use Illuminate\Support\Facades\DB;
use App\Models\{NotificationUser, Project, TicketMessage};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Roles;
;


/**
 * Class BUser
 * @package App\Models
 * @property $id
 * @property $email
 * @property $password
 * @property $is_super_admin
 * @property $first_name
 * @property $last_name
 * @property $phone
 * @property $status
 * @property $two_fa_type
 * @property $google2fa_secret
 * @property NotificationUser[] $comments
 * @property TicketMessage[] $messages
 */
class BUser extends Authenticatable
{
    use HasRoles;

    // tell Eloquent that uuid is a string, not an integer
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];


    protected $fillable = [
        'email', 'first_name', 'last_name', 'phone', 'status', 'two_fa_type', 'google2fa_secret'
    ];

    const ADMIN_REGISTER_CACHE = 'admin_token_';
    /**
     * @return mixed
     */
    //TODO add manager condition
    public static function accountManagersList() {
        return self::pluck('email', 'id')->toArray();
    }

    /**
     * @return mixed
     */
    //TODO add compliance manager condition
    public static function complianceManagersList() {
        return self::pluck('email', 'id')->toArray();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(NotificationUser::class, 'userable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function messages()
    {
        return $this->morphMany(TicketMessage::class, 'massageable');
    }

    public static function getBUser()
    {
        return BUser::query()->where('is_super_admin', AdminRoles::IS_SUPER_ADMIN)->first();
    }

    public function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function getBUsersByStatus($status)
    {
        $bUsers = self::query();

        if (isset($status) && isset(AdminRoles::NAMES_STATUS[$status])) {
            $bUsers->where('status', $status);
        }

        return $bUsers->get();
    }

    public function hasPermissionInAnyProject(array $permissionsArray): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        $roleIds = DB::table('model_has_roles')
            ->where('model_id', $this->id)
            ->groupBy('role_id')
            ->pluck('role_id')->toArray();
        return Role::query()->whereIn('roles.id', $roleIds)
            ->whereHas('permissions', function ($q) use ($permissionsArray){
                return $q->whereIn('permissions.name', $permissionsArray);
            })->exists();
    }

    public function getAvailableProjectsByPermissions(?array $permissions)
    {
        $projects = Project::query()->pluck('id')->toArray();

        $roleIds = Role::query()->whereHas('permissions', function ($q) use ($permissions){
            return $q->whereIn('permissions.name', $permissions);
        })->pluck('roles.id')->toArray();

        return DB::table('model_has_roles')->where([
            'model_id' => $this->id
        ])->whereIn('role_id', $roleIds)
            ->whereIn('project_id', $projects)
            ->groupBy('project_id')
            ->pluck('project_id')->toArray();
    }

    public function hasUserRolesInProject(Project $project)
    {
        setPermissionsTeamId($project->id);
        return !$this->roles->isEmpty();
    }


    public function isAllowed(?array $permissions, ?string $projectId = null)
    {
        if ($projectId) {
            setPermissionsTeamId($projectId);
        }
        return $this->is_super_admin || $this->hasAllPermissions($permissions);
    }
}

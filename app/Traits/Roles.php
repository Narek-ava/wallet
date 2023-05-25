<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasPermissions;

trait Roles
{
    use HasPermissions;

    /** @var string */
    private $roleClass;

    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->roles()->detach();
        });
    }

    public function getRoleClass()
    {
        if (! isset($this->roleClass)) {
            $this->roleClass = app(PermissionRegistrar::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(?string $projectId = null): BelongsToMany
    {
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            PermissionRegistrar::$pivotRole
        );

        if (! PermissionRegistrar::$teams) {
            return $relation;
        }

        $relation = $relation->wherePivot(PermissionRegistrar::$teamsKey, getPermissionsTeamId())
            ->where(function ($q) {
                $teamField = config('permission.table_names.roles').'.'.PermissionRegistrar::$teamsKey;
                $q->whereNull($teamField)->orWhere($teamField, getPermissionsTeamId());
            });

        if (!is_null($projectId)) {
            $relation = $relation->wherePivot('project_id', $projectId);
        }

        return  $relation;
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string $guard
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof Role) {
                return $role;
            }

            $method = is_numeric($role) ? 'findById' : 'findByName';

            return $this->getRoleClass()->{$method}($role, $guard ?: $this->getDefaultGuardName());
        }, Arr::wrap($roles));

        return $query->whereHas('roles', function (Builder $subQuery) use ($roles) {
            $roleClass = $this->getRoleClass();
            $key = (new $roleClass())->getKeyName();
            $subQuery->whereIn(config('permission.table_names.roles').".$key", \array_column($roles, $key));
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param array|string|int|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection ...$roles
     *
     * @return $this
     */
    public function assignRole(?string $projectId = null, ...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->reduce(function ($array, $role) {
                if (empty($role)) {
                    return $array;
                }

                $role = $this->getStoredRole($role);
                if (! $role instanceof Role) {
                    return $array;
                }

                $this->ensureModelSharesGuard($role);

                $array[$role->getKey()] = PermissionRegistrar::$teams && ! is_a($this, Permission::class) ?
                    [PermissionRegistrar::$teamsKey => getPermissionsTeamId()] : [];

                return $array;
            }, []);

        $model = $this->getModel();

        if ($model->exists) {
            $projectIdsArray = array_fill(0, count($roles), ['project_id' => $projectId]);
            $this->roles($projectId)->sync(array_combine($roles, $projectIdsArray), false);
            $model->load('roles');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model, $projectId) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }

                    $model->roles($projectId)->sync(array_combine($roles, ['project_id' => $projectId]), false);
                    $model->load('roles');
                }
            );
        }

        if (is_a($this, get_class($this->getPermissionClass()))) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param string|int|\Spatie\Permission\Contracts\Role $role
     */
    public function removeRole($role, ?string $projectId = null)
    {
        $this->roles($projectId)->detach($this->getStoredRole($role));

        $this->load('roles');

        if (is_a($this, get_class($this->getPermissionClass()))) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param  array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection|string|int  ...$roles
     *
     * @return $this
     */
    public function syncRoles(?string $projectId = null,...$roles)
    {
        $this->roles($projectId)->detach();

        return $this->assignRole($projectId, $roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string|null $guard
     * @return bool
     */
    public function hasRole($roles, string $guard = null, ?string $projectId = null): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $guard
                ? $this->roles($projectId)->get()->where('guard_name', $guard)->contains('name', $roles)
                : $this->roles($projectId)->get()->contains('name', $roles);
        }

        if (is_int($roles)) {
            $roleClass = $this->getRoleClass();
            $key = (new $roleClass())->getKeyName();

            return $guard
                ? $this->roles($projectId)->get()->where('guard_name', $guard)->contains($key, $roles)
                : $this->roles($projectId)->get()->contains($key, $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles($projectId)->get()->contains($roles->getKeyName(), $roles->getKey());
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role, $guard, $projectId)) {
                    return true;
                }
            }

            return false;
        }

        return $roles->intersect($guard ? $this->roles($projectId)->get()->where('guard_name', $guard) : $this->roles($projectId)->get())->isNotEmpty();
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * Alias to hasRole() but without Guard controls
     *
     * @param string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole(?string $projectId = null, ...$roles): bool
    {
        return $this->hasRole($roles, null, $projectId);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param  string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection  $roles
     * @param  string|null  $guard
     * @return bool
     */
    public function hasAllRoles($roles, string $guard = null, ?string $projectId = null): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $guard
                ? $this->roles($projectId)->get()->where('guard_name', $guard)->contains('name', $roles)
                : $this->roles($projectId)->get()->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles($projectId)->get()->contains($roles->getKeyName(), $roles->getKey());
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect(
                $guard
                    ? $this->roles($projectId)->get()->where('guard_name', $guard)->pluck('name')
                    : $this->getRoleNames()
            ) == $roles;
    }

    /**
     * Determine if the model has exactly all of the given role(s).
     *
     * @param  string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection  $roles
     * @param  string|null  $guard
     * @return bool
     */
    public function hasExactRoles($roles, string $guard = null, ?string $projectId = null): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if ($roles instanceof Role) {
            $roles = [$roles->name];
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $this->roles($projectId)->count() == $roles->count() && $this->hasAllRoles($roles, $guard, $projectId);
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getRoleNames(?string $projectId = null): Collection
    {
        return $this->roles($projectId)->pluck('name');
    }

    protected function getStoredRole($role): Role
    {
        $roleClass = $this->getRoleClass();

        if (is_numeric($role)) {
            return $roleClass->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $roleClass->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}

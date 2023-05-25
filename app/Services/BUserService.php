<?php

namespace App\Services;

use App\Facades\EmailFacade;
use App\Models\Backoffice\BUser;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BUserService
{
    public function createUser(array $dataArray)
    {
        $bUser = new BUser();
        $id = Str::uuid();
        $bUser->id = $id;
        $bUser->fill($dataArray);
        $bUser->save();

        $token = Str::random();
        $data = [
            'userId' => $id,
            'token' => $token,
        ];
        $this->putDataIntoCache($token, $data);

        EmailFacade::sendPasswordSetEmailAdmin($token, $bUser->email);
    }

    public function putDataIntoCache(string $key, array $data)
    {
        Cache::put(BUser::ADMIN_REGISTER_CACHE . $key, $data, 1800);
    }

   public function getDataFromCache(string $key): array
    {
        return Cache::get(BUser::ADMIN_REGISTER_CACHE . $key) ?? [];
    }

   public function deleteDataFromCache(string $key)
    {
        return Cache::forget(BUser::ADMIN_REGISTER_CACHE . $key);
    }

    public function getBUserByToken($token)
    {
        $data = $this->getDataFromCache($token);
        if (empty($data) || !isset($data['userId'])) {
            return null;
        }
        $BUserId = $data['userId'];

        $BUser = BUser::findOrFail($BUserId);

        return $BUser;
    }

    public function setNewPassword(string $token, string $password)
    {
        $BUser = $this->getBUserByToken($token);

        if (!$BUser) {
            return false;
        }

        $BUser->password = Hash::make($password);
        $BUser->save();

        return $this->deleteDataFromCache($token);
    }


    public function getPaginatedAllAvailableRoles()
    {
        return Role::query()->paginate(10);
    }

    public function getAllAvailableRoleNames()
    {
        setPermissionsTeamId(null);
        return Role::pluck('name');
    }

    public function getAllPermissionNames()
    {
        return Permission::query()->pluck('name')->toArray();
    }

    public function getBUsersWithRolesForProject(Project $project)
    {
        $users = [];
        $bUsers = BUser::all();
        foreach ($bUsers as $bUser) {
            if ($bUser->hasUserRolesInProject($project)) {
                setPermissionsTeamId($project->id);
                $users[$bUser->id] = $bUser->roles->pluck('name')->toArray();
            }
        }

        return $users;
    }

    public function getManagersByProject(Project $project)
    {
        return BUser::query()->whereIn('id', array_keys($this->getBUsersWithRolesForProject($project)))->get();
    }
}

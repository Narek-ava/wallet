<?php


namespace App\Services;


use App\Models\Backoffice\BUser;

class BUsersService
{

    public function getUserIdsArray(?string $projectId = null)
    {
        $query = BUser::query();

        if ($projectId) {
            setPermissionsTeamId($projectId);
            $query->whereHas('roles')->orWhere('is_super_admin', true);
        }

        return $query->pluck('id')->toArray();
    }

    public function getUserEmailsArray(?string $projectId = null)
    {
        $query = BUser::query();

        if ($projectId) {
            setPermissionsTeamId($projectId);
            $query->whereHas('roles')->orWhere('is_super_admin', true);
        }

        return $query->pluck('email')->toArray();
    }
}

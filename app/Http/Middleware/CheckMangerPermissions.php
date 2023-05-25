<?php

namespace App\Http\Middleware;

use App\Enums\ProjectStatuses;
use App\Models\Account;
use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\CardAccountDetail;
use App\Models\ClientSystemWallet;
use App\Models\CryptoAccountDetail;
use App\Models\Operation;
use App\Models\PaymentForm;
use App\Models\Project;
use Closure;

class CheckMangerPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, string $permissions)
    {
        $permissionsArray = explode('-', $permissions);

        /* @var BUser $manager */
        $manager = auth()->guard('bUser')->user();

        if ($manager->is_super_admin) {
            config()->set('projects.currentPermissions', $permissionsArray);
            return $next($request);
        }

        $projectId = $request->get('project_id');
        if (!$projectId) {
            $findProjectAttemptsMethods = [
                'getByCProfile', 'getByCryptoAccountDetail', 'getByAccount', 'getByOperation', 'getByPaymentForm',
                'getByClientWallet', 'getByCUser'
//            'getByProject', 'getByCUser', 'getByCryptoAccountDetail', 'getByCardAccountDetail',
                // ... to be continued!
            ];

            if (!in_array('GET', $request->route()->methods())) {
                $findProjectAttemptsMethods[] = 'getByProject';
            }

            $project = $this->findProject(request()->route()->parameters(), $findProjectAttemptsMethods);
            if (!$project) {
                $project = $this->findProject(request()->all(), $findProjectAttemptsMethods);
            }

            $projectId = $project->id ?? null;
        }


        $hasAllPermissions = false;
        if ($projectId) {
            setPermissionsTeamId($projectId);
            if ($manager->hasAllPermissions($permissionsArray)) {
                $hasAllPermissions = true;
            }
        } else {
            $projectIds = $this->getProjectsByManager($manager);
            foreach ($projectIds as $project) {
                setPermissionsTeamId($project);
                if($manager->hasAllPermissions($permissionsArray)) {
                    $hasAllPermissions = true;
                    break;
                }
            }
        }

        if(!$hasAllPermissions) {
            session()->flash('error', t('permission_error'));
            return redirect()->back();
        }

        config()->set('projects.currentPermissions', $permissionsArray);
        return $next($request);
    }

    protected function findProject(array $requestParamsArray, array $findProjectAttemptsMethods)
    {
        foreach ($requestParamsArray as $key => $value) {
            foreach ($findProjectAttemptsMethods as $method) {
                if (is_array($value) || is_object($value)) {
                    if (is_object($value)) {
                        $value = $value->toArray();
                    }
                    $project = $this->findProject($value, $findProjectAttemptsMethods);
                } else {
                    $project = $value ? $this->$method($value) : null;
                }
                if ($project) {
                    return $project;
                }
            }
        }
        return null;
    }


    protected function getByCProfile(string $param)
    {
        $cProfile = CProfile::find($param);
        return $cProfile->cUser->project ?? null;
    }

    protected function getByCUser(string $param)
    {
        $cUser = CUser::find($param);
        return $cUser->project ?? null;
    }

    protected function getByOperation($param)
    {
        $operation = Operation::find($param);
        return $operation->cProfile->cUser->project ?? null;
    }

    protected function getByCardAccountDetail($param)
    {
        $cardAccountDetails = CardAccountDetail::find($param);
        return $cardAccountDetails->account->cProfile->cUser->project ?? null;
    }

    protected function getByPaymentForm($param)
    {
        $paymentForm = PaymentForm::find($param);
        return $paymentForm->project ?? null;
    }

    protected function getByClientWallet($param)
    {
        $clientWallet = ClientSystemWallet::find($param);
        return $clientWallet->project ?? null;
    }

    protected function getByProject($param)
    {
        return Project::find($param);
    }

    protected function getByCryptoAccountDetail($param)
    {
        $cryptoAccountDetails = CryptoAccountDetail::find($param);
        return $cryptoAccountDetails->account->cProfile->cUser->project ?? null;
    }

    protected function getByAccount($param)
    {
        $account = Account::find($param);
        return $account->cProfile->cUser->project ?? null;
    }

    /**
     * @param BUser $bUser
     * @return array
     */
    protected function getProjectsByManager(BUser $bUser):array
    {
        $ids = [];
        $projects = Project::query()->where('status', ProjectStatuses::STATUS_ACTIVE)->get();
        foreach ($projects as $project) {
            if ($bUser->hasUserRolesInProject($project)) {
                $ids[] = $project->id;
            }
        }

        return $ids;
    }
}

<?php

namespace App\Providers;

use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Services\ComplianceProviderService;
use App\Services\ComplianceService;
use App\Services\SumSubService;
use App\Services\Wallester\Api;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SumSubServiceProvider extends ServiceProvider implements DeferrableProvider
{


    protected function getByProject($param)
    {
        return \App\Models\Project::find($param);
    }

    protected function getByCUser($param)
    {
        $cUser = CUser::find($param);
        return $cUser->project ?? null;
    }

    protected function getByCProfile($param)
    {
        $cProfile = CProfile::find($param);
        return $cProfile->cUser->project ?? null;
    }

    protected function findProject(array $requestParamsArray, array $findProjectAttemptsMethods)
    {
        foreach ($requestParamsArray as $value) {
            foreach ($findProjectAttemptsMethods as $method) {
                $project = $this->$method($value);
                if ($project) {
                    return $project;
                }
            }
        }
        return null;
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SumSubService::class, function () {

            if (request()->route()->prefix('cabinet')) {
                $project = \App\Models\Project::getCurrentProject();
            } else {
                $findProjectAttemptsMethods = [
                    'getByProject', 'getByCUser', 'getByCProfile',
                    // ... to be continued!
                ];

                $project = $this->findProject(request()->route()->parameters(), $findProjectAttemptsMethods);
                if (!$project) {
                    $project = $this->findProject(request()->all(), $findProjectAttemptsMethods);
                }


            }

            if (!$project) {
                $project = \App\Models\Project::getCurrentProject();
            }

            return new ComplianceService($project);
        });


    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }


    public function provides()
    {
        return [
            Api::class
        ];
    }
}

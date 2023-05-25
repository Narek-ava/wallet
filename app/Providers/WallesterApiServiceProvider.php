<?php

namespace App\Providers;

use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\CardAccountDetail;
use App\Models\CryptoAccountDetail;
use App\Models\Operation;
use App\Services\Wallester\Api;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class WallesterApiServiceProvider extends ServiceProvider implements DeferrableProvider
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

    protected function getByCryptoAccountDetail($param)
    {
        $cryptoAccountDetails = CryptoAccountDetail::find($param);
        return $cryptoAccountDetails->account->cProfile->cUser->project ?? null;
    }

    protected function getByCardAccountDetail($param)
    {
        $cardAccountDetails = CardAccountDetail::find($param);
        return $cardAccountDetails->account->cProfile->cUser->project ?? null;
    }

    protected function getByOperation($operationId)
    {
        $operation = Operation::find($operationId);
        return $operation->cProfile->cUser->project ?? null;
    }

    protected function findProject(array $requestParamsArray, array $findProjectAttemptsMethods)
    {
        foreach ($requestParamsArray as $key => $value) {
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
        $this->app->bind(Api::class, function () {

            $project = null;
            if(request()->route()) {
                if (request()->route()->getPrefix() === 'cabinet') {
                    $project = \App\Models\Project::getCurrentProject();
                } else {
                    $findProjectAttemptsMethods = [
                        'getByProject', 'getByCUser', 'getByCryptoAccountDetail', 'getByCardAccountDetail', 'getByCProfile', 'getByOperation'
                        // ... to be continued!
                    ];

                    $project = $this->findProject(request()->route()->parameters(), $findProjectAttemptsMethods);
                    if (!$project) {
                        $project = $this->findProject(request()->all(), $findProjectAttemptsMethods);
                    }
                }
            }

            if (!$project) {
                $project = \App\Models\Project::getCurrentProject();
            }

            return new Api($project);
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

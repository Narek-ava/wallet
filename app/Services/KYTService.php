<?php

namespace App\Services;

use App\Models\ComplianceProvider;
use App\Models\KytProviders;
use App\Models\Project;
use App\ProjectKytProviders;

class KYTService
{
    public function getProvidersActive(?string $projectId = null)
    {
        return KytProviders::where([
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->queryByProject($projectId)->orderBy('id', 'desc')->get();
    }

    public function providerStore($data)
    {
        $paymentProvider = new KytProviders();

        $paymentProvider->fill($data);
        $paymentProvider->save();

        return $paymentProvider;
    }

    public function updateProvider(string $providerId, string $name, string $api, string $apiAccount, int $status)
    {
        $provider = KytProviders::query()->findOrFail($providerId);
        $provider->update([
            'name' => $name,
            'api' => $api,
            'api_account' => $apiAccount,
            'status' => $status,
        ]);

        return $provider;
    }

    public function getProviderById($id)
    {
        return KytProviders::query()->where('id', $id)->first();
    }
    public function updateProjectsKYTProvider(string $projectId, int $providerId)
    {
       $kyt = ProjectKytProviders::query()->where('project_id',$projectId);
//       dd($kyt->pivot);
//        dd($kyt);
        if ($kyt->first()){
            $kyt->update(['kyt_provider_id' => $providerId]);
        }else{
            $kyt = new ProjectKytProviders();
            $kyt->project_id = $projectId;
            $kyt->kyt_provider_id = $providerId;
            $kyt->save();
        }

        return $kyt;
    }


}

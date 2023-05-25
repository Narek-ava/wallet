<?php


namespace App\Services;


use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Enums\Providers;
use App\Models\Cabinet\CProfile;
use App\Models\ComplianceProvider;
use App\Models\PaymentProvider;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ComplianceProviderService
{

    public function providerStore($data)
    {

        /** @var ComplianceProvider  $paymentProvider */
        $paymentProvider = new ComplianceProvider();

        $paymentProvider->fill($data);
        $paymentProvider->save();

        return $paymentProvider;
    }

    public function getProvidersActive(?string $projectId = null)
    {
        return ComplianceProvider::where([
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->queryByProject($projectId)->orderBy('id', 'desc')->get();
    }

    public function getProviders()
    {
        return ComplianceProvider::query()->orderBy('id', 'desc')->get();
    }

    public function getProviderById($id)
    {
        return ComplianceProvider::query()->where('id', $id)->first();
    }

    public function updateProvider($data)
    {
        /** @var ComplianceProvider $provider */
        $provider = ComplianceProvider::findOrFail($data['provider_id']);
        $provider->update($data);
        return $provider;
    }

    public function getProviderForProject(string $projectId)
    {
        /** @var Project $project */
        $project = Project::find($projectId);
        return $project->complianceProvider() ?? null;
    }

}

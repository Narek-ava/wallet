<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\KYTProviderRequest;
use App\Http\Requests\ComplianceProviderRequest;
use App\Models\KytProviders;
use App\Services\ComplianceProviderService;
use App\Services\KYTService;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class KYTProvidrsController extends Controller
{
    public function index(KYTService $providerService, ProjectService $projectService)
    {
        $providers = $providerService->getProvidersActive();
        $activeProvidersFirstId = null;
        if ($providers->count()){
            $activeProvidersFirstId = $providers->first()->id;
        }
        $providerId = old('payment_provider_id') ?? $activeProvidersFirstId;
        if (session()->has('payment_provider_id')) {
            $providerId = session()->get('payment_provider_id') ?? $providerId;
        }
        $activeProjects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        $complianceProviders = config('kyt_providers.providers');

        return view('backoffice.providers.kyt', compact('providerId', 'providers', 'activeProjects', 'complianceProviders'));
    }

    public function store(KYTProviderRequest $request, KYTService $providerService)
    {
        $provider = $providerService->providerStore($request->validated());
        return redirect()->back()->with([
            'success' => t('provider_compliance_new'),
            'payment_provider_id' => $provider->id]);
    }
    public function getAccountByProvider(string $providerKey)
    {
        $kytProviders = config('kyt_providers.providers');
        $accounts = isset($kytProviders[$providerKey]) ? array_keys($kytProviders[$providerKey]) : [];

        return response()->json(['accounts' => $accounts]);
    }

    public function providerUpdate(KYTProviderRequest $request, KYTService $providerService)
    {
//        dd($request->all());
        $provider = $providerService->updateProvider(
            $request->get('provider_id'),
            $request->get('name'),
            $request->get('api'),
            $request->get('api_account'),
            $request->get('status'),
        );

        return redirect()->back()->with([
            'success' => t('provider_ky_update'),
            'payment_provider_id' => $provider->id]);
    }

    public function getProvider($providerId, KYTService $providerService)
    {
        return $providerService->getProviderById($providerId);
    }

    public function getProviders()
    {
        return KytProviders::query()->orderBy('id', 'desc')->get();
    }


}

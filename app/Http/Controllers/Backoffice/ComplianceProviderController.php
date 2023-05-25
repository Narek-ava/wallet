<?php
namespace App\Http\Controllers\Backoffice;

use App\Enums\AccountType;
use App\Enums\CardSecure;
use App\Enums\PaymentSystemTypes;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Enums\TemplateType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ComplianceProviderRequest;
use App\Http\Requests\CreateCardProviderAccountRequest;
use App\Http\Requests\ProviderRequest;
use App\Services\AccountCountriesService;
use App\Services\AccountService;
use App\Services\CardAccountService;
use App\Services\CommissionsService;
use App\Services\ComplianceProviderService;
use App\Services\LimitsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\WireAccountService;
use Illuminate\Http\Request;

class ComplianceProviderController extends Controller
{
    public function index(ComplianceProviderService $providerService, ProjectService $projectService)
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
        $complianceProviders = config('compliance_providers.providers');

        return view('backoffice.providers.compliance', compact('providerId', 'providers', 'activeProjects', 'complianceProviders'));
    }

    public function store(ComplianceProviderRequest $request, ComplianceProviderService $providerService)
    {
        $provider = $providerService->providerStore($request->validated());
        return redirect()->back()->with([
            'success' => t('provider_compliance_new'),
            'payment_provider_id' => $provider->id]);
    }

    public function getProvider($providerId, ComplianceProviderService $providerService)
    {
        return $providerService->getProviderById($providerId);
    }

    public function providerUpdate(ComplianceProviderRequest $request, ComplianceProviderService $providerService)
    {
        $provider = $providerService->updateProvider($request->all());

        return redirect()->back()->with([
            'success' => t('provider_compliance_update'),
            'payment_provider_id' => $provider->id]);
    }

    public function getProviders($part, ComplianceProviderService $providerService)
    {
        if ($part === 'all') {
            return $providerService->getProviders();
        }
        return $providerService->getProvidersActive();
    }

    public function getAccountByProvider(string $providerKey)
    {
        $complianceProviders = config('compliance_providers.providers');
        $accounts = isset($complianceProviders[$providerKey]) ? array_keys($complianceProviders[$providerKey]) : [];

        return response()->json(['accounts' => $accounts]);
    }
}

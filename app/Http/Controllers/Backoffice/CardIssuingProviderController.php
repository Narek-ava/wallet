<?php


namespace App\Http\Controllers\Backoffice;


use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderRequest;
use App\Models\WallesterAccountDetail;
use App\Services\AccountService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class CardIssuingProviderController extends Controller
{
    public function index(ProviderService $providerService, AccountService $accountService, ProjectService $projectService, SettingService $settingService)
    {

        $providers = $providerService->getProvidersActive(Providers::PROVIDER_CARD_ISSUING);
        $activeProvidersFirstId = null;
        if ($providers->count()){
            $activeProvidersFirstId = $providers->first()->id;
        }
        $providerId = old('payment_provider_id') ?? $activeProvidersFirstId;
        if (session()->has('payment_provider_id')) {
            $providerId = session()->get('payment_provider_id') ?? $providerId;
        }
        $accounts = $accountService->providerAccounts($providerId);
        $activeProjects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.providers.card-issuing', compact('providers', 'accounts', 'providerId', 'activeProjects'));
    }

    public function store(ProviderRequest $request, ProviderService $providerService)
    {
        return $providerService->providerStore($request->all());
    }

    public function getApiAccounts(Request $request)
    {
        return response()->json([
            'apiAccounts' => !empty($request->api) ? array_keys(config('cardissuing.' . $request->api) ?? []) : [],
        ]);
    }

}

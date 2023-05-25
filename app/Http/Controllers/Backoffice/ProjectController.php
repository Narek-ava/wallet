<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\AdminRoles;
use App\Enums\Currency;
use App\Enums\EmailProviders;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Enums\SmsProviders;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\ProjectCardIssuingSettingsRequest;
use App\Http\Requests\Backoffice\UpdateProjectRequest;
use App\Models\Backoffice\BUser;
use App\Models\Backoffice\ProjectEmailProvider;
use App\Models\Backoffice\ProjectSmsProvider;
use App\Models\BaseModel;
use App\Models\Cabinet\CUser;
use App\Models\ClientSystemWallet;
use App\Models\KytProviders;
use App\Models\Project;
use App\ProjectKytProviders;
use App\Services\BUserService;
use App\Services\ClientSystemWalletService;
use App\Services\ComplianceProviderService;
use App\Services\KYTService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\RateTemplatesService;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::EDIT_PROJECTS]), ['except' => ['index', 'edit']]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ProjectService $projectService)
    {
        $projects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);

        foreach ($projects as $project) {
            $path = '/cratos.theme/' . $project->id;
            if (!file_exists(public_path($path))) {
                mkdir(public_path($path));
            }
        }
        return view('backoffice.projects.index', compact('projects'));
    }

    public function create(BUserService $BUserService, ProviderService $providerService, RateTemplatesService $rateTemplatesService,ComplianceProviderService $complianceProviderService, KYTService $KYTService)
    {
        $bUsers = BUser::getBUsersByStatus(AdminRoles::STATUS_ACTIVE)->pluck('email', 'id');
        $availableRoles = $BUserService->getAllAvailableRoleNames();
        $allWalletProviders = $providerService->getProvidersActive(Providers::PROVIDER_WALLET);
        $allPaymentProviders = $providerService->getProvidersActive(Providers::PROVIDER_PAYMENT);
        $allCardProviders = $providerService->getProvidersActive(Providers::PROVIDER_CARD);
        $allLiquidityProviders = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY);
        $allCardIssuingProviders = $providerService->getProvidersActive(Providers::PROVIDER_CARD_ISSUING);
        $allKYTProviders = $KYTService->getProvidersActive();
        $allSmsProviders = SmsProviders::getList();
        $allEmailProviders = config('mail.email_providers');
        $rates = $rateTemplatesService->getRateTemplatesServiceActive();
        $bankCardRates = $rateTemplatesService->getAllActiveBankCardRates();
        $allComplianceProviders = $complianceProviderService->getProvidersActive();
        $projectLiqProviders = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY)->pluck('name', 'id')->toArray();


        $newProject = true;
        $url = route('projects.store');
        return view('backoffice.projects.edit', compact('bUsers', 'availableRoles', 'allWalletProviders', 'allPaymentProviders',
            'allCardProviders', 'allLiquidityProviders', 'allCardIssuingProviders', 'url', 'allSmsProviders', 'allEmailProviders', 'newProject', 'rates', 'bankCardRates', 'allComplianceProviders', 'projectLiqProviders','allKYTProviders'));
    }

    public function store(UpdateProjectRequest $request, SettingService $settingService)
    {
        $project = new Project();
        $project->name = $request->name;
        $project->status = $request->status;
        $project->domain = $request->domain;
        $project->individual_rate_templates_id = $request->individualRate;
        $project->corporate_rate_templates_id = $request->corporateRate;
        $project->bank_card_rate_templates_id = $request->bankCardRate;
        $project->save();
        $project->refresh();

        $companyDetails = [
            'name' => $request->get('companyName'),
            'country' => $request->get('companyCountry'),
            'city' => $request->get('companyCity'),
            'zip_code' => $request->get('companyZipCode'),
            'address' => $request->get('companyAddress'),
            'license' => $request->get('companyLicense'),
            'registry' => $request->get('companyRegistry'),
            'terms_and_conditions' => $request->get('termsAndConditions'),
            'aml_policy' => $request->get('amlPolicy'),
            'privacy_policy' => $request->get('privacyPolicy'),
            'frequently_asked_question' => $request->get('frequentlyAskedQuestion'),
        ];

        $settingService->createSetting([
            'project_id' => $project->id,
            'key' => $project->id . '_address',
            'project_company_details' => json_encode($companyDetails)
        ]);

        if ($request->logo) {
            $pathName = public_path('/cratos.theme/' . $project->id . '/images/');
            $fileName = 'logo.png';
            if (file_exists($pathName . $fileName)) {
                unlink($pathName . $fileName);
            }
            $request->logo->move($pathName, $fileName);
        }

        if ($request->bUsers) {
            foreach ($request->bUsers as $bUserId) {
                /* @var BUser $bUser */
                $bUser = BUser::findOrFail($bUserId);
                setPermissionsTeamId($project->id);
                $bUser->assignRole($request->roles[$bUserId]);
                $bUser->givePermissionTo();
            }
        }

        $providers = array_merge($request->liquidityProviders, $request->paymentProviders ?? [],
            [$request->liqProvider, $request->walletProvider, $request->cardProvider]);
        if ($request->issuingProvider) {
            $providers[] = $request->issuingProvider;
        }
        if ($request->cardProvider) {
            $providers[] = $request->cardProvider;
        }


        $syncProviders = array_fill_keys($providers, ['is_default' => false]);
        unset($syncProviders['']);

        $syncProviders[$request->liqProvider]['is_default'] = true;
        if ($request->issuingProvider) {
            $syncProviders[$request->issuingProvider]['is_default'] = true;
        }
        if ($request->cardProvider) {
            $syncProviders[$request->cardProvider]['is_default'] = true;
        }

        $syncProviders[$request->walletProvider]['is_default'] = true;

        $project->providers()->sync($syncProviders);

        if($request->complianceProvider) {
            $syncCompliance[$request->complianceProvider]['renewal_interval'] = $request->renewalInterval;
            $project->complianceProviderModel()->sync(
                $syncCompliance
            );
        }

        $project->smsProviders()->delete();

        foreach ($request->smsProviders as $smsProvider) {
            $projectSmsProvider = new ProjectSmsProvider();
            $projectSmsProvider->fill([
                'project_id' => $project->id,
                'key' => $smsProvider
            ]);

            $projectSmsProvider->save();
        }

        $projectEmailProvider = new ProjectEmailProvider();
        $projectEmailProvider->fill([
            'project_id' => $project->id,
            'key' => $request->emailProvider
        ]);


        $projectEmailProvider->save();

        if(isset($request->createClientWallet)) {
            foreach (Currency::getList() as $currency) {
                $clientSystemWallet = new ClientSystemWallet();
                $clientSystemWallet->fill([
                    'wallet_id' => $request->get('walletId'. $currency),
                    'currency' => $request->get('currency' . $currency),
                    'project_id' => $project->id,
                ]);
                if ($request->has('passphrase'. $currency)) {
                    $clientSystemWallet->passphrase = Crypt::encrypt($request->get('passphrase' . $currency));
                }
                $clientSystemWallet->save();
            }
        }

        $projectKYTProvider = new ProjectKytProviders();
        $projectKYTProvider->fill([
            'project_id' => $project->id,
            'kyt_provider_id' => $request->kytProvider
            ]);

        session()->flash('success', t('project_create_success'));
        return redirect()->route('projects.index');

    }

    public function edit(string $id, BUserService $BUserService, ProviderService $providerService, RateTemplatesService $rateTemplatesService, ComplianceProviderService $complianceProviderService,ClientSystemWalletService $clientSystemWalletService, KYTService $KYTService)
    {
        $project = Project::findOrFail($id);

        $bUsers = BUser::getBUsersByStatus(AdminRoles::STATUS_ACTIVE)->pluck('email', 'id');


        /* @var Project $project */
        $usersWithRoles = $BUserService->getBUsersWithRolesForProject($project);

        $availableRoles = $BUserService->getAllAvailableRoleNames();

        $allWalletProviders = $providerService->getProvidersActive(Providers::PROVIDER_WALLET);
        $projectWalletProvider = $providerService->getProviderForProject(Providers::PROVIDER_WALLET, $project->id);

        $allPaymentProviders = $providerService->getProvidersActive();
        $projectPaymentProviders = $providerService->getProvidersActive(Providers::PROVIDER_PAYMENT, $project->id)->pluck('id')->toArray();

        $allCardProviders = $providerService->getProvidersActive(Providers::PROVIDER_CARD);
        $projectCardProvider = $providerService->getProviderForProject(Providers::PROVIDER_CARD, $project->id);

        $allLiquidityProviders = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY);

        $allCardIssuingProviders = $providerService->getProvidersActive(Providers::PROVIDER_CARD_ISSUING);

        $projectDefaultIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);

        $allSmsProviders = SmsProviders::getList();
        $projectSmsProviderKeys = $project->smsProviders()->pluck('key', 'key')->toArray();
        $projectSmsProviders = array_intersect_key($allSmsProviders, $projectSmsProviderKeys);
        $allEmailProviders = config('mail.email_providers');

        $allComplianceProviders = $complianceProviderService->getProvidersActive();
        $allKYTProviders = $KYTService->getProvidersActive();
        $projectComplianceProvider = $project->complianceProvider();
        $projectKytProvider = $project->kytProvider();
        $projectDefaultLiqProvider = $providerService->getDefaultLiquidityProvider($project->id);
        $projectLiqProviders = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY, $project->id)->pluck('name', 'id')->toArray();
        $newProject = false;
        $url = route('projects.update', $project->id);
        $rates = $rateTemplatesService->getRateTemplatesServiceActive();
        $bankCardRates = $rateTemplatesService->getAllActiveBankCardRates();
        $clientWallets = $clientSystemWalletService->getClientSystemWalletsForProjects([$project->id]);

        return view('backoffice.projects.edit', compact('project', 'url',
            'bUsers', 'availableRoles', 'usersWithRoles', 'newProject',
            'allWalletProviders', 'projectWalletProvider', 'allPaymentProviders', 'allCardProviders',
            'projectPaymentProviders', 'projectCardProvider', 'allLiquidityProviders', 'projectDefaultLiqProvider', 'projectLiqProviders',
            'allSmsProviders', 'projectSmsProviders', 'allCardIssuingProviders', 'projectDefaultIssuingProvider', 'allEmailProviders', 'rates', 'bankCardRates','allComplianceProviders', 'projectComplianceProvider', 'clientWallets',
            'allKYTProviders','projectKytProvider'
        ));
    }

    /**
     * @param UpdateProjectRequest $request
     * @param $id
     */
    public function update(UpdateProjectRequest $request, string $id, SettingService $settingService, ProjectService $projectService,KYTService $KYTService)
    {

        $project = Project::findOrFail($id);

        if ($request->logo) {
            $pathName = public_path('/cratos.theme/' . $project->id . '/images/');
            $fileName = 'logo.png';
            if (file_exists($pathName . $fileName)) {
                unlink($pathName . $fileName);
            }

            $request->logo->move($pathName, $fileName);
        }

        $project->name = $request->name;
        $project->status = $request->status;
        $project->individual_rate_templates_id = $request->individualRate;
        $project->corporate_rate_templates_id = $request->corporateRate;
        $project->bank_card_rate_templates_id = $request->bankCardRate;

        $setting = $settingService->getSettingByKey($project->id . '_address', $project->id);
        $companyDetails = [
            'name' => $request->get('companyName'),
            'country' => $request->get('companyCountry'),
            'city' => $request->get('companyCity'),
            'zip_code' => $request->get('companyZipCode'),
            'address' => $request->get('companyAddress'),
            'license' => $request->get('companyLicense'),
            'registry' => $request->get('companyRegistry'),
            'terms_and_conditions' => $request->get('termsAndConditions'),
            'aml_policy' => $request->get('amlPolicy'),
            'privacy_policy' => $request->get('privacyPolicy'),
            'frequently_asked_question' => $request->get('frequentlyAskedQuestion'),
        ];
        if (!$setting) {
             $settingService->createSetting([
                'project_id' => $project->id,
                'key' => $project->id . '_address',
                'project_company_details' => json_encode($companyDetails)
            ]);
        } else {
            $setting->content = $request->address;
            $setting->project_company_details = json_encode($companyDetails);
            $setting->save();
        }
        $project->color_settings = json_encode($request->only(['mainColor', 'buttonColor', 'borderColor', 'notifyFromColor', 'notifyToColor']));

        if ($request->domain !== $project->domain) {
            $project->domain = $request->domain;
        }
        $project->save();

        DB::table('model_has_roles')->where('project_id', $project->id)->delete();

        foreach ($request->bUsers as $bUserId) {
            /* @var BUser $bUser */
            $bUser = BUser::findOrFail($bUserId);
            setPermissionsTeamId($project->id);
            $bUser->assignRole($request->roles[$bUserId]);
        }

        $providers = array_merge($request->liquidityProviders, $request->paymentProviders ?? [],
            [$request->liqProvider, $request->walletProvider]);
        if ($request->issuingProvider) {
            array_push($providers, $request->issuingProvider);
        }
        if ($request->cardProvider) {
            array_push($providers, $request->cardProvider);
        }

        $syncProviders = array_fill_keys($providers, ['is_default' => false]);
        unset($syncProviders['']);
        $syncProviders[$request->liqProvider]['is_default'] = true;
        $syncProviders[$request->walletProvider]['is_default'] = true;
        if ($request->issuingProvider) {
            $syncProviders[$request->issuingProvider]['is_default'] = true;
        }
        if ($request->cardProvider) {
            $syncProviders[$request->cardProvider]['is_default'] = true;
        }

        $project->providers()->sync($syncProviders);

        if($request->complianceProvider) {
            $syncCompliance[$request->complianceProvider]['renewal_interval'] = $request->renewalInterval;
            $project->complianceProviderModel()->sync(
                $syncCompliance
            );
        } else {
            $project->complianceProviderModel()->sync([]);
        }


        $project->smsProviders()->delete();

        foreach ($request->smsProviders as $smsProvider) {
            $projectSmsProvider = new ProjectSmsProvider();
            $projectSmsProvider->fill([
                'project_id' => $project->id,
                'key' => $smsProvider
            ]);

            $projectSmsProvider->save();
        }

        $projectEmailProvider = $project->emailProvider ?? new ProjectEmailProvider();
        $projectEmailProvider->fill([
            'project_id' => $project->id,
            'key' => $request->emailProvider
        ]);
        $projectEmailProvider->save();
        if ($request->kytProvider){
            $KYTService->updateProjectsKYTProvider($id,$request->kytProvider);
        }



        session()->flash('success', t('project_update_success'));
        return redirect()->route('projects.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getProjects(string $part, ProjectService $projectService)
    {
        if ($part == 'all') {
            $projects = Project::all();
        } else {
            $projects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        }

        return response()->json($projects);
    }

    public function checkManagerPermissionsInProject(Request $request)
    {
        /* @var BUser $manager */
        $manager = auth()->guard('bUser')->user();

        setPermissionsTeamId($request->checkingProjectId);

        if ($manager->is_super_admin || $manager->hasPermissionTo($request->permission)) {
            return response()->json([
                'success' => true
            ]);
        }
            return response()->json([
                'permission_error' => t('permission_error')
            ], 403);

    }
}

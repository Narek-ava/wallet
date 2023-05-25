<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\AccountType;
use App\Enums\CardProviderRegions;
use App\Enums\CardSecure;
use App\Enums\PaymentSystemTypes;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Enums\TemplateType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\PaymentProviderRequest;
use App\Http\Requests\CreateProviderAccountRequest;
use App\Http\Requests\ProviderRequest;
use App\Models\AccountClientPolicy;
use App\Models\Country;
use App\Models\PaymentProvider;
use App\Services\AccountCountriesService;
use App\Services\AccountService;
use App\Services\CommissionsService;
use App\Services\LimitsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\WireAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentProviderController extends Controller
{
    public function index(ProviderService $providerService, AccountService $accountService, ProjectService $projectService)
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
        $accounts = $accountService->providerAccounts($providerId);
        $activeProjects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        return view('backoffice.providers.index', compact('providers', 'accounts', 'providerId', 'activeProjects'));
    }

    public function store(ProviderRequest $request, ProviderService $providerService)
    {
        return $providerService->providerStore($request->all());
    }

    public function getProviders($part, $page, ProviderService $providerService)
    {
        if ($part === 'all') {
            return $providerService->getProviders($page);
        }
        return $providerService->getProvidersActive($providerService->getProviderType($page));
    }

    public function addProviderAccount(CreateProviderAccountRequest $request,
                                       AccountService $accountService,
                                       LimitsService $limitsService,
                                       WireAccountService $wireAccountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService)
    {

        try {
            if ($request->makeCopy) {
                $request->offsetUnset('name');
                $request->offsetSet('name', $request->copyName);
            }
            $accountData = $request->only(['payment_provider_id','typeAccount', 'country', 'currency', 'name', 'statusAccount', 'fiat_type']);
            $accountData['owner_type'] = AccountType::ACCOUNT_OWNER_TYPE_SYSTEM;
            $accountData['account_type'] = $accountData['typeAccount'];
            unset($accountData['typeAccount']);
            $accountData['status'] = $accountData['statusAccount'];
            unset($accountData['statusAccount']);
            $account = $accountService->createAccount($accountData);
            $commissionsService->createCommissions($account, 'name', $request->only(['percent_commission','fixed_commission','min_commission', 'max_commission']));
            $accountCountriesService->createCountries($request->countries, $account->id);
            if($request->wireAccountType) {
                foreach ($request->wireAccountType as $accountType) {
                    $accountClientPolicy = new AccountClientPolicy();
                    $accountClientPolicy->account_id = $account->id;
                    $accountClientPolicy->type = $accountType;
                    $accountClientPolicy->save();
                }
            }
            $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max', 'transaction_amount_min']);
            $limitData['account_id'] = $account->id;
            $limitsService->createLimit($limitData);
            $wireAccountData = $request->only(['account_number', 'account_beneficiary', 'beneficiary_address', 'time_to_found',
                'iban', 'swift', 'bank_name', 'bank_address', 'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']);
            $wireAccountData['account_id'] = $account->id;
            $wireAccountService->createWireAccount($wireAccountData);
            return redirect()->back()->with([
                'success' => t('provider_successfully_added'),
                'payment_provider_id' => $account->payment_provider_id
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'warning' => $e->getMessage(),
                'payment_provider_id' => $account->payment_provider_id
            ]);
        }
    }

    public function getProviderAccounts($providerId, AccountService $accountService)
    {
        $accounts = $accountService->providerAccounts($providerId);
        if ($accounts) {
            foreach ($accounts as $account) {
                $account->updateBalance();
                if ($account->provider->provider_type == Providers::PROVIDER_CARD) {
                    $account->type =  t(TemplateType::NAMES[$account->cardAccountDetail->type]);
                    $account->cardAccountDetail->paymentSystemName = PaymentSystemTypes::getName($account->cardAccountDetail->payment_system);
                    $account->cardAccountDetail->cardSecureName = CardSecure::getName($account->cardAccountDetail->secure);
                    $account->cardAccountDetail->regionName = CardProviderRegions::getName($account->cardAccountDetail->region);
                    $account->statusName = t(\App\Enums\AccountStatuses::STATUSES[$account->status]);
                }else {
                    $account->type = t(\App\Enums\TemplateType::NAMES[$account->account_type]);
                    $account->currency = $account->currency ? $account->currency : $account->cryptoAccountDetail->coin;
                    $account->country = Country::getCountryNameByCode($account->country);
                    $account->status = t(\App\Enums\AccountStatuses::STATUSES[$account->status]);
                }
                $account->detailViewLink = route('dashboard.account',['account' => $account->id]);
                $account->formatedBalance = generalMoneyFormat($account->balance, $account->currency);
            }
        }
        return $accounts;
    }

    public function putProviderAccount(CreateProviderAccountRequest $request,
                                       AccountService $accountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService)
    {
        try {
            $account = $accountService->getAccountById($request->account_id);
            $accountData = $request->only(['payment_provider_id','typeAccount', 'country', 'currency', 'name', 'statusAccount', 'minimum_balance_alert', 'fiat_type']);
            $accountData['account_type'] = $accountData['typeAccount'];
            unset($accountData['typeAccount']);
            $accountData['status'] = $accountData['statusAccount'];
            unset($accountData['statusAccount']);
            $account->fill($accountData);
            if ($account->isDirty('name') && $account->childAccount) {
                $account->childAccount->update(['name' => $account->name . ' Commissions']);
            }
            if ( $account->isDirty('currency')&& $account->childAccount) {
                $account->childAccount->update(['currency' => $account->currency]);
            }
            $account->update();
            $commissionsService->updateProviderCommission($account->id, $request->only(['percent_commission','fixed_commission','min_commission', 'max_commission']));
            $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max', 'transaction_amount_min']);
            $account->limit()->update($limitData);
            $wireAccountData = $request->only(['account_number', 'account_beneficiary', 'beneficiary_address', 'time_to_found',
                'iban', 'swift', 'bank_name', 'bank_address', 'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']);
            $account->wire()->update($wireAccountData);
            $accountCountriesService->updateCountries($request->countries, $account);
            $account->accountClientPolicy()->delete();
            if($request->wireAccountType) {
                foreach ($request->wireAccountType as $accountType) {
                    $accountClientPolicy = new AccountClientPolicy();
                    $accountClientPolicy->account_id = $account->id;
                    $accountClientPolicy->type = $accountType;
                    $accountClientPolicy->save();
                }
            }
            return redirect()->back()->with('success', t('provider_successfully_changed'));
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }
    }

    public function getProvider($providerId, ProviderService $providerService)
    {
        return $providerService->getProviderWithProjects($providerId);
    }

    public function providerUpdate(ProviderRequest $request, ProviderService $providerService)
    {
        return $providerService->updateProvider($request->all());
    }

}

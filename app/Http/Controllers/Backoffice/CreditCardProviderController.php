<?php
namespace App\Http\Controllers\Backoffice;

use App\Enums\AccountType;
use App\Enums\CardSecure;
use App\Enums\PaymentSystemTypes;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Enums\TemplateType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCardProviderAccountRequest;
use App\Services\AccountCountriesService;
use App\Services\AccountService;
use App\Services\CardAccountService;
use App\Services\CommissionsService;
use App\Services\LimitsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\WireAccountService;
use Illuminate\Http\Request;

class CreditCardProviderController extends Controller
{
    public function index(ProviderService $providerService, AccountService $accountService, ProjectService $projectService)
    {
        $providers = $providerService->getProvidersActive(Providers::PROVIDER_CARD);
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

        return view('backoffice.providers.credit-card', compact('providerId', 'providers', 'accounts', 'activeProjects'));
    }

    public function addProviderAccount(CreateCardProviderAccountRequest $request,
                                       AccountService $accountService,
                                       LimitsService $limitsService,
                                       WireAccountService $wireAccountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService,
                                       CardAccountService $cardAccountService)
    {
        try {
           $accountData = $request->only(['account_type', 'payment_provider_id', 'name', 'statusAccount', 'country', 'currency']);
           $accountData['owner_type'] = AccountType::ACCOUNT_OWNER_TYPE_SYSTEM;
            $accountData['status'] = $accountData['statusAccount'];
            unset($accountData['statusAccount']);
           $account = $accountService->createAccount($accountData);
           $commissionsService->createCommissions($account, 'name', $request->only(['percent_commission','fixed_commission','min_commission', 'max_commission']));
           $accountCountriesService->createCountries($request->countries, $account->id);
           $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max']);
           $limitData['account_id'] = $account->id;
           $limitsService->createLimit($limitData);
           $wireAccountData = $request->only(['account_beneficiary', 'beneficiary_address', 'time_to_found', 'iban', 'swift', 'bank_name', 'bank_address' ,
               'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']);
           $wireAccountData['account_id'] = $account->id;
           $wireAccountService->createWireAccount($wireAccountData);
           $cardAccountDetail = $request->only(['card_type', 'region', 'secure', 'payment_system']);
           $cardAccountDetail['account_id'] = $account->id;
           $cardAccountDetail['type'] = $cardAccountDetail['card_type'];
           unset($cardAccountDetail['card_type']);
           $cardAccountService->createCardDetail($cardAccountDetail);
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

    public function putProviderAccount(CreateCardProviderAccountRequest $request,
                                       AccountService $accountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService,
                                       WireAccountService $wireAccountService,
                                       CardAccountService $cardAccountService)
    {
       try {
           $account = $accountService->getAccountById($request->account_id);
           $accountData = $request->only(['payment_provider_id', 'name', 'statusAccount', 'account_type', 'country', 'currency']);
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
           $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max']);
           $account->limit()->update($limitData);
           $accountCountriesService->updateCountries($request->countries, $account);
           $wireAccountData = $request->only(['account_beneficiary', 'beneficiary_address', 'time_to_found', 'iban', 'swift', 'bank_name', 'bank_address' , 'correspondent_bank',
               'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']);
           $account->wire()->update($wireAccountData);
           $cardAccountDetail = $request->only(['card_type', 'region', 'secure', 'payment_system']);
           $cardAccountDetail['type'] = $cardAccountDetail['card_type'];
           unset($cardAccountDetail['card_type']);
           $account->cardAccountDetail()->update($cardAccountDetail);
           return redirect()->back()->with('success', t('provider_successfully_changed'));
       } catch (\Exception $e) {
           return redirect()->back()->with('warning', $e->getMessage());
       }
    }

    public function getPaymentSystem()
    {
        foreach (PaymentSystemTypes::NAMES as $key => $system) {
            $paymentSystems[$key] = PaymentSystemTypes::getName($key);
        }

        return $paymentSystems ?? null;
    }

    public function getApiAccounts(Request $request)
    {
        return response()->json([
            'apiAccounts' => !empty($request->api) ? array_keys(config('cardproviders.' . $request->api) ?? []) : [],
        ]);
    }


}

<?php
namespace App\Http\Controllers\Backoffice;

use App\Enums\AccountType;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiquidityProviderAccountBtcRequest;
use App\Http\Requests\LiquidityProviderAccountSepaRequest;
use App\Services\AccountCountriesService;
use App\Services\AccountService;
use App\Services\CommissionsService;
use App\Services\CryptoAccountService;
use App\Services\CrytptoAccountService;
use App\Services\LimitsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\WireAccountService;
use Illuminate\Http\Request;
use function GuzzleHttp\Promise\all;

class LiquidityProviderController extends Controller
{
    public function index(ProviderService $providerService, AccountService $accountService, ProjectService $projectService)
    {
        $providers = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY);
        $activeProvidersFirstId = null;
        if ($providers->count()){
            $activeProvidersFirstId = $providers->first()->id;
        }
        $providerLiquidity = old('payment_provider_id') ?? old('btc_payment_provider_id');
        $providerId = $providerLiquidity ?? $activeProvidersFirstId;
        if (session()->has('payment_provider_id')) {
            $providerId = session()->get('payment_provider_id') ?? $providerId;
        }
        $accounts = $accountService->providerAccounts($providerId);
        $activeProjects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.providers.liquidity', compact('providerId', 'providers', 'accounts', 'activeProjects'));
    }

    public function addProviderAccountSepa(LiquidityProviderAccountSepaRequest $request,
                                       AccountService $accountService,
                                       LimitsService $limitsService,
                                       WireAccountService $wireAccountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService,
                                       CryptoAccountService $cryptoAccountService)
    {
        try {
            $accountData = $request->only(['payment_provider_id','typeAccount', 'country', 'currency', 'name', 'statusAccount', 'minimum_balance_alert']);
            $accountData['owner_type'] = AccountType::ACCOUNT_OWNER_TYPE_SYSTEM;
            $accountData['account_type'] = $accountData['typeAccount'];
            unset($accountData['typeAccount']);
            $accountData['status'] = $accountData['statusAccount'];
            unset($accountData['statusAccount']);
            $account = $accountService->createAccount($accountData);
            $commissionsService->createCommissions($account, 'name', $request->only(['percent_commission','fixed_commission','min_commission', 'max_commission']));
            $accountCountriesService->createCountries($request->countries, $account->id);
            $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max', 'transaction_amount_min']);
            $limitData['account_id'] = $account->id;
            $limitsService->createLimit($limitData);
            $wireAccountData = $request->only(['account_number', 'account_beneficiary', 'beneficiary_address', 'time_to_found', 'iban', 'swift', 'bank_name', 'bank_address',
                'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']);
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

    public function addProviderAccountBtc(LiquidityProviderAccountBtcRequest $request,
                                       AccountService $accountService,
                                       LimitsService $limitsService,
                                       WireAccountService $wireAccountService,
                                       CommissionsService $commissionsService,
                                       CryptoAccountService $cryptoAccountService)
    {
        try {
            $accountData = $request->only(['btc_payment_provider_id','btc_typeAccount', 'btc_name', 'btc_statusAccount', 'btc_currency', 'btc_minimum_balance_alert']);
            $accountData['owner_type'] = AccountType::ACCOUNT_OWNER_TYPE_SYSTEM;
            $accountData['currency'] = $accountData['btc_currency'];
            unset($accountData['btc_currency']);
            $accountData['account_type'] = $accountData['btc_typeAccount'];
            unset($accountData['btc_typeAccount']);
            $accountData['status'] = $accountData['btc_statusAccount'];
            unset($accountData['btc_statusAccount']);
            $accountData['name'] = $accountData['btc_name'];
            unset($accountData['btc_name']);
            $accountData['payment_provider_id'] = $accountData['btc_payment_provider_id'];
            unset($accountData['btc_payment_provider_id']);
            $accountData['minimum_balance_alert'] = $accountData['btc_minimum_balance_alert'];
            unset($accountData['btc_minimum_balance_alert']);
            $account = $accountService->createAccount($accountData);
            $commissionsService->createCommissions($account, 'name', $request->only(['btc_fixed_commission','btc_percent_commission','btc_min_commission', 'btc_max_commission', 'blockchain_fee']), 'btc_');
            $limitData = $request->only(['btc_transaction_amount_max', 'btc_monthly_amount_max', 'btc_transaction_count_daily_max', 'btc_transaction_count_monthly_max', 'btc_transaction_amount_min']);
            $limitData['transaction_amount_max'] = $limitData['btc_transaction_amount_max'];
            unset($limitData['btc_transaction_amount_max']);
            $limitData['monthly_amount_max'] = $limitData['btc_monthly_amount_max'];
            unset($limitData['btc_monthly_amount_max']);
            $limitData['transaction_count_daily_max'] = $limitData['btc_transaction_count_daily_max'];
            unset($limitData['btc_transaction_count_daily_max']);
            $limitData['transaction_count_monthly_max'] = $limitData['btc_transaction_count_monthly_max'];
            unset($limitData['btc_transaction_count_monthly_max']);
            $limitData['transaction_amount_min'] = $limitData['btc_transaction_amount_min'];
            unset($limitData['btc_transaction_amount_min']);
            $limitData['account_id'] = $account->id;
            $limitsService->createLimit($limitData);
            $wireAccountData = $request->only(['btc_time_to_found']);
            $wireAccountData['time_to_found'] = $wireAccountData['btc_time_to_found'];
            unset($wireAccountData['btc_time_to_found']);
            $wireAccountData['account_id'] = $account->id;
            $wireAccountService->createWireAccount($wireAccountData);
            $cryptoAccountDetails = $request->only(['btc_crypto_wallet', 'btc_currency']);
            $cryptoAccountDetails['address'] = $cryptoAccountDetails['btc_crypto_wallet'];
            unset($cryptoAccountDetails['btc_crypto_wallet']);
            $cryptoAccountDetails['coin'] = $cryptoAccountDetails['btc_currency'];
            unset($cryptoAccountDetails['btc_currency']);
            $cryptoAccountDetails['account_id'] = $account->id;
            $cryptoAccountDetails['wallet_data'] = json_encode([]);
            $cryptoAccountService->createCryptoAccount($cryptoAccountDetails);
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

    public function putProviderAccountSepa(LiquidityProviderAccountSepaRequest $request,
                                       AccountService $accountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService)
    {
        try {
            $account = $accountService->getAccountById($request->account_id);
            $accountData = $request->only(['payment_provider_id','typeAccount', 'country', 'currency', 'name', 'statusAccount', 'minimum_balance_alert']);
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
            $wireAccountData = $request->only(['account_number', 'account_beneficiary', 'beneficiary_address', 'time_to_found', 'iban', 'swift', 'bank_name', 'bank_address',
                'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']);
            $account->wire()->update($wireAccountData);
            $accountCountriesService->updateCountries($request->countries, $account);
            return redirect()->back()->with('success', t('provider_successfully_changed'));
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }
    }

    public function putProviderAccountBtc(LiquidityProviderAccountBtcRequest $request,
                                       AccountService $accountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService)
    {
        try {
            $account = $accountService->getAccountById($request->account_id);
            $accountData = $request->only(['btc_payment_provider_id','btc_typeAccount', 'btc_name', 'btc_statusAccount', 'btc_minimum_balance_alert']);
            $accountData['account_type'] = $accountData['btc_typeAccount'];
            unset($accountData['btc_typeAccount']);
            $accountData['status'] = $accountData['btc_statusAccount'];
            unset($accountData['btc_statusAccount']);
            $accountData['name'] = $accountData['btc_name'];
            unset($accountData['btc_name']);
            $accountData['payment_provider_id'] = $accountData['btc_payment_provider_id'];
            unset($accountData['btc_payment_provider_id']);
            $accountData['minimum_balance_alert'] = $accountData['btc_minimum_balance_alert'];
            unset($accountData['btc_minimum_balance_alert']);
            $account->fill($accountData);
            if ($account->isDirty('name') && $account->childAccount) {
                $account->childAccount->update(['name' => $account->name . ' Commissions']);
            }
            $account->update();
            $commissionsService->updateProviderCommission($account->id, $request->only(['btc_percent_commission','btc_fixed_commission','btc_min_commission', 'btc_max_commission', 'blockchain_fee']), 'btc_');
            $limitData = $request->only(['btc_transaction_amount_max', 'btc_monthly_amount_max', 'btc_transaction_count_daily_max', 'btc_transaction_count_monthly_max', 'btc_transaction_amount_min']);
            $limitData['transaction_amount_max'] = $limitData['btc_transaction_amount_max'];
            unset($limitData['btc_transaction_amount_max']);
            $limitData['monthly_amount_max'] = $limitData['btc_monthly_amount_max'];
            unset($limitData['btc_monthly_amount_max']);
            $limitData['transaction_count_daily_max'] = $limitData['btc_transaction_count_daily_max'];
            unset($limitData['btc_transaction_count_daily_max']);
            $limitData['transaction_count_monthly_max'] = $limitData['btc_transaction_count_monthly_max'];
            unset($limitData['btc_transaction_count_monthly_max']);
            $limitData['transaction_amount_min'] = $limitData['btc_transaction_amount_min'];
            unset($limitData['btc_transaction_amount_min']);
            $account->limit()->update($limitData);
            $wireAccountData = $request->only(['btc_time_to_found']);
            $wireAccountData['time_to_found'] = $wireAccountData['btc_time_to_found'];
            unset($wireAccountData['btc_time_to_found']);
            $account->wire()->update($wireAccountData);
            $cryptoAccountDetails = $request->only(['btc_crypto_wallet', 'btc_currency']);
            $cryptoAccountDetails['address'] = $cryptoAccountDetails['btc_crypto_wallet'];
            unset($cryptoAccountDetails['btc_crypto_wallet']);
            $cryptoAccountDetails['coin'] = $cryptoAccountDetails['btc_currency'];
            unset($cryptoAccountDetails['btc_currency']);
            $account->cryptoAccountDetail()->update($cryptoAccountDetails);
            return redirect()->back()->with('success', t('provider_successfully_changed'));
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }
    }

    public function getApiAccounts(Request $request)
    {
        return response()->json([
            'apiAccounts' => !empty($request->api) ? array_keys(config('liquidityproviders.' . $request->api) ?? []) : []
        ]);
    }
}

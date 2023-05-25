<?php
namespace App\Http\Controllers\Backoffice;

use App\Enums\AccountType;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Http\Controllers\Controller;
use App\Http\Requests\WalletProviderAccountRequest;
use App\Models\Project;
use App\Services\AccountCountriesService;
use App\Services\AccountService;
use App\Services\CommissionsService;
use App\Services\CProfileService;
use App\Services\CryptoAccountService;
use App\Services\LimitsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\WireAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class WalletProviderController extends Controller
{
    public function index(ProviderService $providerService, AccountService $accountService, CProfileService $CProfileService, ProjectService $projectService)
    {
        $providers = $providerService->getProvidersActive(Providers::PROVIDER_WALLET);
        $activeProvidersFirstId = null;
        if ($providers->count()){
            $activeProvidersFirstId = $providers->first()->id;
        }
        $providerId = old('payment_provider_id') ?? $activeProvidersFirstId;
        if (session()->has('payment_provider_id')) {
            $providerId = session()->get('payment_provider_id') ?? $providerId;
        }
        $accounts = $accountService->providerAccounts($providerId);
        $profiles = $CProfileService->getProfilesDropdown();

        $activeProjects =  $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        $api_accounts = array_keys(config('walletproviders.bitgo'));

        return view('backoffice.providers.wallet', compact('providerId', 'providers', 'accounts', 'profiles', 'activeProjects', 'api_accounts'));
    }

    public function getApiAccounts()
    {
        return response()->json([
            'apiAccounts' => array_keys(config('walletproviders.bitgo') ?? []),
        ]);
    }

    public function addProviderAccount(WalletProviderAccountRequest $request,
                                       AccountService $accountService,
                                       LimitsService $limitsService,
                                       WireAccountService $wireAccountService,
                                       CommissionsService $commissionsService,
                                       CryptoAccountService $cryptoAccountService)
    {
        try {
            $accountData = $request->only(['payment_provider_id', 'currency', 'name', 'statusAccount']);
            $accountData['owner_type'] = AccountType::ACCOUNT_OWNER_TYPE_SYSTEM;
            $accountData['account_type'] = AccountType::TYPE_CRYPTO;
            $accountData['id'] = Str::uuid()->toString();
            $accountData['status'] = $accountData['statusAccount'];
            unset($accountData['statusAccount']);
            $account = $accountService->createAccount($accountData);
            $commissionsService->createCommissions($account, 'name', $request->only(['percent_commission','fixed_commission','min_commission','max_commission', 'blockchain_fee']));
            $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max', 'transaction_amount_min']);
            $limitData['account_id'] = $account->id;
            $limitsService->createLimit($limitData);
            $wireAccountData = $request->only(['time_to_found']);
            $wireAccountData['account_id'] = $account->id;
            $wireAccountService->createWireAccount($wireAccountData);
            $cryptoAccountDetails = $request->only(['crypto_wallet', 'currency', 'wallet_id', 'label_in_kraken']);
            $cryptoAccountDetails['address'] = $cryptoAccountDetails['crypto_wallet'];
            unset($cryptoAccountDetails['crypto_wallet']);
            $cryptoAccountDetails['coin'] = $cryptoAccountDetails['currency'];
            unset($cryptoAccountDetails['currency']);
            $cryptoAccountDetails['account_id'] = $account->id;
            $cryptoAccountDetails['wallet_data'] = json_encode([]);
            $cryptoAccountDetails['passphrase'] = $request->passphrase ? Crypt::encrypt($request->passphrase) : null;
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

    public function putProviderAccount(WalletProviderAccountRequest $request,
                                       AccountService $accountService,
                                       AccountCountriesService $accountCountriesService,
                                       CommissionsService $commissionsService,
                                       CryptoAccountService $cryptoAccountService)
    {
        try{
            $account = $accountService->getAccountById($request->account_id);
            $accountData = $request->only(['payment_provider_id','currency', 'name', 'statusAccount']);
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
            $commissionsService->updateProviderCommission($account->id, $request->only(['percent_commission','fixed_commission','min_commission', 'max_commission', 'blockchain_fee']));
            $limitData = $request->only(['transaction_amount_max', 'monthly_amount_max', 'transaction_count_daily_max', 'transaction_count_monthly_max', 'transaction_amount_min']);
            $account->limit()->update($limitData);
            $wireAccountData = $request->only('time_to_found');
            $account->wire()->update($wireAccountData);
            $accountCountriesService->updateCountries($request->countries, $account);
            $cryptoAccountDetails = $request->only(['crypto_wallet', 'currency', 'wallet_id', 'label_in_kraken']);
            $cryptoAccountDetails['address'] = $cryptoAccountDetails['crypto_wallet'];
            unset($cryptoAccountDetails['crypto_wallet']);
            $cryptoAccountDetails['coin'] = $cryptoAccountDetails['currency'];
            unset($cryptoAccountDetails['currency']);
            if ($request->passphrase) {
                $cryptoAccountDetails['passphrase'] = Crypt::encrypt($request->passphrase);
            }
            $account->cryptoAccountDetail()->update($cryptoAccountDetails);
            return redirect()->back()->with('success', t('provider_successfully_changed'));
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }
    }
}

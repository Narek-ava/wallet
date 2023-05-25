<?php

namespace App\Services;


use App\Enums\{AccountStatuses,
    AccountType,
    AnalyticSystems,
    Currency,
    LogMessage,
    LogResult,
    LogType,
    OperationOperationType,
    PaymentProvider,
    Providers,
    TemplateType};
use App\Models\{Account, AccountClientPolicy, Backoffice\BUser, Cabinet\CProfile, Operation};
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\{Facades\Auth, Facades\Cache, Facades\DB, Facades\Http, Str};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AccountService
{

    const CONFIG_RISK_SCORE = 'cratos.accounts.risk_score';
    const CONFIG_RISK_SCORE_DAYS = 'cratos.accounts.risk_score_days';
    const CONFIG_RISK_SCORE_DAYS_FOR_0 = 'cratos.accounts.risk_score_days_for_0';

    /**
     *
     * @param $data
     * @param $currency
     * @return string
     */
    public function getAccount($data, $currency): string
    {
        $account = Account::query()->where($this->findAccountData($data, $currency))->get()->first();
        if (!$account) {
            $account = Account::query()->create($this->createAccountData($data, $currency));
        }
       return $account->id;
    }

    public function findAccountData($data, $currency)
    {

        $data = [
            ['type', '=', $data['wire_type']],
            ['currency', '=', $currency],
            ['country', '=', $data['country'] ?? ''],
            ['c_profile_id', '=', Auth::user()->cProfile->id],
            ['holder', '=', $data['holder'] ?? ''],
            ['number', '=', $data['number'] ?? ''],
            ['bank_name', '=', $data['bank_name'] ?? '' ],
            ['bank_address', '=', $data['bank_address'] ?? '' ],
            ['IBAN', '=', $data['iban'] ?? '' ],
            ['SWIFT', '=', $data['swift'] ?? '' ],
        ];

        return $data;

    }


    public function createAccountData($data, $currency)
    {

        $data = [
            'id' => Str::uuid(),
            'type' => $data['wire_type'],
            'country' => $data['country'] ?? '',
            'currency' => $currency,
            'c_profile_id' => Auth::user()->cProfile->id,
            'holder' =>  $data['holder'] ?? '',
            'number' =>  $data['number'] ?? '',
            'bank_name' =>  $data['bank_name'] ?? '',
            'bank_address' =>  $data['bank_address'] ?? '',
            'IBAN' =>  $data['iban'] ?? '',
            'SWIFT' =>  $data['swift'] ?? ''
        ];

        return $data;

    }

    public function createAccount($data)
    {
        $data['id'] = Str::uuid()->toString();
        $account = Account::create($data);

        $account->refresh();

        if ($account->provider->provider_type != Providers::PROVIDER_PAYMENT && in_array($account->currency, Currency::FIAT_CURRENCY_NAMES)) {
            foreach (AccountType::WIRE_PROVIDER_TYPES as $type) {
                $policy  = new AccountClientPolicy();
                $policy->account_id = $account->id;
                $policy->type = $type;
                $policy->save();
            }
        }

        $data['id'] = Str::uuid()->toString();
        $data['balance'] = 0;
        $data['parent_id'] = $account->id;
        $data['name'] = $data['name'] . ' Commissions';
        $data['owner_type'] = AccountType::ACCOUNT_OWNER_TYPE_PROVIDER;
        Account::create($data);
        return $account;
    }

    public function providerAccounts($providerId)
    {
        if ($providerId) {
            return Account::with(['wire', 'cryptoAccountDetail'])->where(['payment_provider_id' => $providerId, 'parent_id' => null, 'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM])->orderBy('updated_at', 'desc')->get();
        }
        return [];
    }

    public function getAccountById($id)
    {
        return Account::with(['cardAccountDetail', 'cryptoAccountDetail', 'countries', 'wire', 'fromCommission', 'toCommission', 'internalCommission', 'refundCommission', 'limit', 'chargebackCommission'])->find($id);
    }

    public function updateStatus($status, $accounts)
    {
        if ($status == PaymentProvider::STATUS_DISABLED) {
            foreach ($accounts as $account) {
                $account->update(['status' => $status]);
            }
        }
    }

    public function getUserBankAccountsByCProfileId($cProfileId, ?array $dataArray = null)
    {
        $query = Account::whereHas('wire')->where([
            'c_profile_id' =>  $cProfileId,
            'is_external' => AccountType::ACCOUNT_EXTERNAL,
            'status' => AccountStatuses::STATUS_ACTIVE,
        ]);

        if (isset($dataArray['country'])) {
            $query->where('country', $dataArray['country']);
        }

        if (isset($dataArray['currency'])) {
            $query->where('currency', $dataArray['currency']);
        }

        if (isset($dataArray['accountType'])) {
            $query->where('account_type', $dataArray['accountType']);
        }

        return $query->get();
    }

    public function getUserCryptoAccountsByCProfileId($cProfileId)
    {
        // @todo artak
        return Account::whereHas('cryptoAccountDetail', function ($q) {
            return $q->where('verified_at', '>=', Carbon::now()->subDays(30)->toDateTimeString())->where('risk_score', '<=', config(self::CONFIG_RISK_SCORE));
        })->where([
            'status' => AccountStatuses::STATUS_ACTIVE,
            'c_profile_id' =>  $cProfileId,
            'is_external' => AccountType::ACCOUNT_EXTERNAL
        ])->get();
    }

    public function createClientCryptoAccount($address, $currency, $riskScore, $status, $cProfileId)
    {
        $account = new Account([
            'name' => 'External '.$currency.' Account',
            'c_profile_id' => $cProfileId,
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
            'currency' => $currency,
            'is_external' => true,
            'status' => $status,
        ]);
        $account->save();
        $cryptoAccountService = new CryptoAccountService();
        $cryptoAccountService->createCryptoAccountDetail($account, $address, $riskScore);
        return $account;
    }

    /**
     * add wallet to client
     * @param string $address
     * @param string $currency
     * @param CProfile $profile
     * @param bool $allowSaveDraft
     * @param string|null $amount
     * @return Account|null
     */
    public function addWalletToClient(string $address, string $currency, CProfile $profile, bool $allowSaveDraft = false, string $amount = null): ?Account
    {
        $account = $profile->accounts()
            ->where('currency', $currency)
            ->where('is_external', true)
            ->whereHas('cryptoAccountDetail', function ($q) use ($currency, $address) {
                $q->where([
                    'coin' => $currency,
                    'address' => $address
                ]);
            })->first();

        $isNewAccount = false;

        $kytProvider = $profile->cUser->project->kytProvider() ?? null;
        $kytProvider = $kytProvider->api ?? AnalyticSystems::ANALYTIC_SUMSUB; //@todo Is kyt provider required ?

        if (!$account) {
            $status = AccountStatuses::STATUS_DISABLED;
            $account = $this->createClientCryptoAccount($address, $currency, null, $status, $profile->id);
            $isNewAccount = true;
        }

        $cryptoDetails = $account->cryptoAccountDetail;

        $sumSubService = new SumSubService();

        if ($isNewAccount || $cryptoDetails->isRiskScoreCheckTime()) {
            if ($kytProvider === AnalyticSystems::ANALYTIC_SUMSUB) {
                $riskScore = $sumSubService->getRisk($address, $currency);
            } elseif ($kytProvider === AnalyticSystems::ANALYTIC_CHAINALYSIS) {
                /* @var ChainalysisService $chainalysisService*/
                $chainalysisService = resolve(ChainalysisService::class);
                $riskScore = $chainalysisService->getRiskScore(
                    $account,
                    $address,
                    $currency,
                    $amount,
                );
            }
            $isValidRisk = $sumSubService->isValidRisk($riskScore);
            $status = $isValidRisk ? AccountStatuses::STATUS_ACTIVE : AccountStatuses::STATUS_DISABLED;
            $cryptoDetails->verified_at = Carbon::now();
            $account->status = $status;
            $cryptoDetails->risk_score = $riskScore;
            $account->save();
            $cryptoDetails->save();
        }

        if (!$cryptoDetails->isAllowedRisk() && !$cryptoDetails->isRiskScoreCheckTime()) {
            logger()->error('CryptoAccountHighRisk');
            return $account;
        }

        if ($isNewAccount && $status == AccountStatuses::STATUS_ACTIVE) {
            EmailFacade::sendAddingCryptoWallet($profile->cUser, $address);
            ActivityLogFacade::saveLog(LogMessage::USER_CRYPTO_ACCOUNT_ADDED, ['account_id' => $account->id, 'wire_account_detail_id' => $account->cryptoAccountDetail->id], LogResult::RESULT_SUCCESS, LogType::TYPE_USER_CRYPTO_ACCOUNT_ADDED);
        }

        return $account;
    }

    public function disabledAccount($currency, $walletAddress, $profileId)
    {
        return Account::where('c_profile_id', $profileId)
            ->whereHas('cryptoAccountDetail', function ($q) use ($currency, $walletAddress) {
                $q->where([
                    'coin' => $currency,
                    'address' => $walletAddress,
                    'status' => AccountStatuses::STATUS_DISABLED
                ]);
            })->first();
    }


    public function getAllowedAccountsByTrxTypeForOperation(int $providerType, Operation $operation, ?string $currency)
    {
        $projectId = $operation->cProfile->cUser->project_id ?? null;
        $query = Account::query()
            ->where([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'currency' => $currency,
                'status' => AccountStatuses::STATUS_ACTIVE
            ])->whereNotNull('name')
            ->whereHas('provider', function ($q) use ($providerType, $projectId) {
                return $q->where([
                    'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE,
                    'provider_type' => $providerType
                ])->queryByProject($projectId);
            });

        $isPaymentProvider = $providerType == Providers::PROVIDER_PAYMENT;

        if ($isPaymentProvider) {
            $type = null;

            if ($operation->cProfile->account_type == CProfile::TYPE_INDIVIDUAL) {
                if (in_array($operation->operation_type, OperationOperationType::TYPES_TOP_UP)) {
                    $type = AccountType::WIRE_PROVIDER_C2B;
                } elseif (in_array($operation->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
                    $type = AccountType::WIRE_PROVIDER_B2C;
                }
            } elseif (in_array($operation->operation_type, array_merge(OperationOperationType::TYPES_WIRE_LAST, OperationOperationType::TYPES_TOP_UP))) {
                $type = AccountType::WIRE_PROVIDER_B2B;
            }

            $query->whereHas('accountClientPolicy', function ($q) use ($type) {
                if ($type) {
                    $q->where('type', $type);
                }
                return $q;
            });
        }

        return $query->get();
    }

    public function getSepaAndSwiftAccountsPagination()
    {
        return Account::where(['owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM])->whereNotNull('payment_provider_id')->paginate(config('cratos.pagination.accounts'));
    }

    public function checkPaymentProviderAccountBalanceAndNotify()
    {
        Account::where('minimum_balance_alert', '!=', null)
            ->chunk(5, function ($accounts) {
                foreach ($accounts as $account) {
                    if ($account->balance < $account->minimum_balance_alert){
                        if (!Cache::has($this->getAccountCacheName($account))) {
                            EmailFacade::sendPaymentProviderAccountBalanceLower(BUser::getBUser(), $account);
                        }
                        Cache::put($this->getAccountCacheName($account), $account->name, 24 * 3600);
                    } else {
                        Cache::forget($this->getAccountCacheName($account));
                    }
                }
            });
    }

    private function getAccountCacheName(Account $account)
    {
        return config('cache.provider.min-balance').$account->id;
    }

    public function toProviderAccounts($providerType, $currency)
    {
        $accounts = [];
        if ($providerType && $currency && (int)$providerType == Providers::PROVIDER_PAYMENT) {
            $accounts = Account::whereHas('provider', function ($q) use ($providerType){
               $q->where('provider_type', $providerType);
            })->select(['id', 'name'])->where('currency', $currency)->get();
        }
        return $accounts;
    }


    public function changeAccountStatus($accountId, $status): void
    {
        $account = Account::findOrFail($accountId);
        $provider = $account->provider;
        if ($provider->status != $status) {
            $provider->status = $status;
            $provider->save();
        }
    }


    public function getPaymentProviderAccounts(
        int $accountType,
        string $countryCode,
        string $currency,
        int $operationType,
        int $profileAccountType,
        ?string $projectId = null,
        int $fiatType = AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT
    )
    {
        $accounts = [];
        $collection = Account::query()
            ->filterPaymentProviderAccounts($accountType, $countryCode, $currency, $operationType, $profileAccountType, $projectId, $fiatType)
            ->get();

        foreach ($collection as $account) {
            $accounts[] = $account;
        }

        return $accounts;

    }

    public function getPaymentProviderById(string $id, int $accountType, string $countryCode, string $currency, int $operationType, int $profileAccountType, ?string $projectId = null, int $fiatType = AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT)
    {
        return Account::query()->where('id', $id)
        ->filterPaymentProviderAccounts($accountType, $countryCode, $currency, $operationType, $profileAccountType, $projectId, $fiatType)
        ->first();
    }

}

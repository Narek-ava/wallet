<?php


namespace App\Services;


use App\Enums\{AccountStatuses, AccountType, OperationOperationType, Providers, TransactionType};
use App\Facades\KrakenFacade;
use App\Models\{Account, Cabinet\CProfile, Operation, PaymentProvider};
use Illuminate\Database\Eloquent\Builder;

class WithdrawWireService
{

    public function getToAccounts(Operation $operation)
    {
        $projectId = $operation->cProfile->cUser->project_id ?? null;
        config()->set('projects.project', $operation->cProfile->cUser->project);
        $provider = $operation->getSelectedExchangeProvider();
        $queryParam = [
            'provider_type' => Providers::PROVIDER_LIQUIDITY,
            'status' => AccountStatuses::STATUS_ACTIVE
        ];
        if ($provider) {
            $queryParam['api'] = $provider->api;
        }

        $allLiquidityProviders = PaymentProvider::where($queryParam)->queryByProject($projectId)->get();
        foreach ($allLiquidityProviders as $aLiquidityProvider) {
            if ($operation->from_currency && $operation->step <= 1) {
                $accountsOfProviderFromCurrency = $aLiquidityProvider->accounts()->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('currency', $operation->from_currency)->where('status', AccountStatuses::STATUS_ACTIVE)->get();
                foreach ($accountsOfProviderFromCurrency as $anAccount) {
                    $liquidityCryptoAccounts[$anAccount->id] = $anAccount;
                    $liquidityCryptoProvidersAccounts[$anAccount->id] = $anAccount->provider;
                }
            }
            if ($operation->to_currency && $operation->step >= 1 && $operation->step <= 2) {
                $accountsOfProviderToCurrency = $aLiquidityProvider->accounts()->where('status', AccountStatuses::STATUS_ACTIVE)
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('currency', $operation->to_currency)->get();
                foreach ($accountsOfProviderToCurrency as $providerAccount) {
                    $liquidityFiatAccounts[$providerAccount->id] = $providerAccount;
                    $liquidityFiatProvidersAccounts[$providerAccount->id] = $providerAccount->provider;
                }
            }
        }
        $paymentProviders = [];
        $paymentProvidersAccounts = [];
        if ($operation->step >= 2) {
            $allPaymentProviders = PaymentProvider::where('provider_type', Providers::PROVIDER_PAYMENT)
                ->queryByProject($projectId)
                ->where('status', AccountStatuses::STATUS_ACTIVE)->get();
            foreach ($allPaymentProviders as $aPaymentProvider) {
                if ($operation->to_currency) {
                    $query = $aPaymentProvider->accounts();
                    if ($operation->cProfile->account_type == CProfile::TYPE_INDIVIDUAL){
                        $query->whereHas('accountClientPolicy', function (Builder $q) use ($operation) {
                            if (in_array($operation->operation_type, OperationOperationType::TYPES_TOP_UP)){
                                $q->where('type', AccountType::WIRE_PROVIDER_C2B);
                            } elseif (in_array($operation->operation_type, OperationOperationType::TYPES_WIRE_LAST)){
                                $q->where('type', AccountType::WIRE_PROVIDER_B2C);
                            }
                        });
                    }
                    if ($operation->cProfile->account_type == CProfile::TYPE_CORPORATE &&
                        in_array($operation->operation_type, array_merge(OperationOperationType::TYPES_WIRE_LAST, OperationOperationType::TYPES_TOP_UP))){
                        $query->whereHas('accountClientPolicy', function (Builder $q) {
                            $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                        });
                    }
                    $accountsOfProviderToCurrency = $query
                        ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                        ->where('currency', $operation->to_currency)->get();
                    foreach ($accountsOfProviderToCurrency as $providerAccount) {
                        $paymentProviders[$providerAccount->id] = $providerAccount;
                        $paymentProvidersAccounts[$providerAccount->id] = $providerAccount->provider;
                    }
                }
            }
        }
        switch ($operation->step) {
            case 0:
                return
                    [
                        'from' => [$operation->fromAccount],
                        'toProviders' => $liquidityCryptoProvidersAccounts ?? null,
                        'to' => $liquidityCryptoAccounts ?? null,
                        'fromCurrency' => $operation->from_currency,
                    ];
                break;
            case 1:
                $exchangeRate = KrakenFacade::ticker($operation->from_currency, $operation->to_currency);
                $transaction = $operation->transactions
                    ->where('type',TransactionType::CRYPTO_TRX)
                    ->where('from_account', $operation->from_account)
                    ->first();
                return [
                    'from' => $liquidityCryptoAccounts ?? null,
                    'to' => $liquidityFiatAccounts ?? null,
                    'toProviders' => $liquidityFiatProvidersAccounts ?? null,
                    'fromProviders' => $liquidityCryptoProvidersAccounts ?? null,
                    'exchangeRate' => $exchangeRate ?? null,
                    'fromCurrency' => $operation->from_currency,
                    'recipientAmount' => $transaction ? getCorrectAmount($transaction->recipient_amount, $operation->from_currency) : null,
                ];
                break;
            case 2:
                $selectedPaymentProvider = $operation->getProviderAccount();
                $exchangeTransaction = $operation->transactions->where('type', TransactionType::EXCHANGE_TRX)->first();
                return [
                    'from' => $liquidityFiatAccounts ?? null,
                    'fromProviders' => $liquidityFiatProvidersAccounts ?? null,
                    'toProviders' => $paymentProvidersAccounts,
                    'to' => $paymentProviders,
                    'selectedPaymentProvider' => $selectedPaymentProvider,
                    'exchangeRate' => null,
                    'fromCurrency' => $operation->to_currency,
                    'recipientAmount' => $exchangeTransaction->recipient_amount,
                ];
                break;
            case 3:
                $account = $operation->toAccount;
                $bankTransactionAmount = $operation->transactions->where('type', TransactionType::BANK_TRX)->first()->recipient_amount;
                $systemCommission = $operation->transactions->where('type', TransactionType::SYSTEM_FEE)
                    ->where('from_account', $operation->provider_account_id)
                    ->first();

                $systemCommissionAmount = $systemCommission->recipient_amount ?? 0;

                return [
                    'from' => $paymentProviders,
                    'fromProviders' => $paymentProvidersAccounts,
                    'to' => [$account],
                    'fromCurrency' => $operation->to_currency,
                    'recipientAmount' => $bankTransactionAmount -  $systemCommissionAmount,
                ];
                break;
        }
    }

    public function getAllowedFromAccounts(Operation $operation, $fromCurrency = null, $paymentProviderIds = null, $fromProviderType = Providers::PROVIDER_PAYMENT)
    {

        $projectId = $operation->cProfile->cUser->project_id ?? null;

        $query = Account::query()
            ->where([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'currency' => $fromCurrency ?? $operation->from_currency,
                'status' => AccountStatuses::STATUS_ACTIVE
            ])->whereHas('provider', function ($q) use ($projectId) {
                return $q->queryByProject($projectId);
            });

        if ($fromProviderType == Providers::PROVIDER_PAYMENT) {
            if ($operation->cProfile->account_type == CProfile::TYPE_INDIVIDUAL){
                $type = null;

                if (in_array($operation->operation_type, OperationOperationType::TYPES_TOP_UP)) {
                    $type = AccountType::WIRE_PROVIDER_C2B;
                } elseif (in_array($operation->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
                    $type = AccountType::WIRE_PROVIDER_B2C;
                }

                $query->whereHas('accountClientPolicy', function (Builder $q) use ($type) {
                    if ($type) {
                        $q->where('type', $type);
                    }
                    return $q;
                });

            } elseif ($operation->cProfile->account_type == CProfile::TYPE_CORPORATE &&
                in_array($operation->operation_type, array_merge(OperationOperationType::TYPES_WIRE_LAST, OperationOperationType::TYPES_TOP_UP))){
                $query->whereHas('accountClientPolicy', function (Builder $q) {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                });
            }
        }

        if ($paymentProviderIds){
            $query->whereIn('payment_provider_id', $paymentProviderIds);
        }

        return $query->get();
    }


}

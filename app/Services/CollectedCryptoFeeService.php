<?php

namespace App\Services;

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\OperationSubStatuses;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Models\Account;
use App\Models\BaseModel;
use App\Models\CollectedCryptoFee;
use App\Models\CryptoAccountDetail;
use App\Models\Operation;
use App\Models\Transaction;
use Carbon\Carbon;

class CollectedCryptoFeeService
{
    public function saveCollectedCryptoFee(float $amount, Account $clientAccount, Account $systemAccount, Transaction $transaction, bool $is_collected = false)
    {
        /* @var ClientSystemWalletService $clientSystemWalletService */
        $clientSystemWalletService = resolve(ClientSystemWalletService::class);

        if($clientSystemWalletService->checkIsClientWalletSystemWallet($clientAccount)) {
            $collectedCryptoFee = new CollectedCryptoFee();
            $collectedCryptoFee->fill([
                'amount' => $amount,
                'client_account_id' => $clientAccount->id,
                'system_account_id' => $systemAccount->id,
                'wallet_id' => $clientAccount->cryptoAccountDetail->wallet_id,
                'transaction_id' => $transaction->id,
                'is_collected' => $is_collected,
                'currency' => $systemAccount->currency,
            ]);
            $collectedCryptoFee->save();

            return $collectedCryptoFee;
        }

        return  null;
    }

    public function getPaginatedCollectedCryptoFees(?string $projectId = null)
    {
        return CollectedCryptoFee::query()->where(function ($q) use ($projectId) {
            return $q->whereNull('transaction_id')
                ->orWhereHas('transaction.operation', function ($query) use ($projectId) {
                    return $query->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::RETURNED])
                        ->whereHas('cProfile.cUser', function ($q) use ($projectId) {
                            $q->where('project_id', $projectId);
                        });
                });
        })->orderBy('created_at')->paginate(10);
    }

    public function getTotalCollectedAmount(?string $from = null, ?string $to = null, ?bool $isCollected = null, ?string $projectId = null)
    {
        return CollectedCryptoFee::query()
            ->filterCollectedTransactions($from, $to, $isCollected, $projectId)
            ->groupBy('currency')
            ->selectRaw('sum(amount) as sum, currency')
            ->pluck('sum', 'currency');
    }

    public function getNotCollectedCryptoFees(?string $from = null, ?string $to = null, ?string $projectId = null)
    {
        return CollectedCryptoFee::query()
            ->filterCollectedTransactions($from, $to, \App\Enums\CollectedCryptoFee::IS_NOT_COLLECTED, $projectId)
            ->get()->groupBy('currency')->all();
    }

    public function getNotCollectedTransactionsCurrencies()
    {
        return  CollectedCryptoFee::query()
            ->where('is_collected', \App\Enums\CollectedCryptoFee::IS_NOT_COLLECTED)
            ->groupBy('currency')
            ->pluck('currency')->toArray();
    }

    public function makeOperation(array $dataArray)
    {
        $externalAccount = $this->getExternalAccount($dataArray['currency'], $dataArray['toAddress']);

        /* @var OperationService $operationService */
        $operationService = resolve(OperationService::class);

        /* @var TransactionService $transactionService */
        $transactionService = resolve(TransactionService::class);

        /* @var CommissionsService $commissionService */
        $commissionService = resolve(CommissionsService::class);

        $systemAccount = Account::getSystemAccount($dataArray['currency'], AccountType::TYPE_CRYPTO);
        try {
            $providerAccount = Account::getProviderAccount($dataArray['currency'], Providers::PROVIDER_WALLET);

            $fromCommission = $providerAccount->getAccountCommission(true);
            $feeAmount = $commissionService->calculateCommissionAmount($fromCommission, $dataArray['amount']);

            $feeSummary = $feeAmount + ($fromCommission->blockchain_fee ?? 0);
            $transAmount = $dataArray['amount'] - $feeSummary;

            if ($transAmount <= 0) {
                $exception = new OperationException( t('collect_fee_amount_is_not_enough', [
                    'amount' => $feeSummary,
                    'currency' => $dataArray['currency']
                ]));

                throw $exception;
            }

            $operation = $operationService->createOperation(null,
                OperationOperationType::TYPE_SYSTEM_FEE_WITHDRAW, $dataArray['amount'],
                $dataArray['currency'], $dataArray['currency'], $systemAccount->id, $externalAccount->id);

            $operation->project_id = $dataArray['project_id'];
            $operation->save();


            $transaction = $transactionService->createTransactions(
                TransactionType::CRYPTO_TRX, $transAmount,
                $systemAccount, $externalAccount,
                Carbon::now(), TransactionStatuses::PENDING,
                null, $operation,
                $fromCommission->id, null,
                'System Account', 'External account',
            );

            $transactionService->createTransactions(
                TransactionType::SYSTEM_FEE,
                $feeAmount,
                $operation->fromAccount,
                $providerAccount->childAccount,
                $operation->created_at,
                TransactionStatuses::SUCCESSFUL,
                null, $operation, null, null, null, null, null, null, $transaction);

            if ($fromCommission->blockchain_fee) {
                $transactionService->createTransactions(
                    TransactionType::BLOCKCHAIN_FEE,
                    $fromCommission->blockchain_fee,
                    $operation->fromAccount,
                    $providerAccount->childAccount,
                    $operation->created_at,
                    TransactionStatuses::SUCCESSFUL,
                    null, $operation, null, null, null, null, null, null, $transaction);
            }


            /* @var ClientSystemWalletService $clientSystemWalletService */
            $clientSystemWalletService = resolve(ClientSystemWalletService::class);

            $sharedWallet = $clientSystemWalletService->getSystemWalletByCurrency($systemAccount->currency, $operation->project_id);

            $cryptoAccountDetail = new CryptoAccountDetail();
            $cryptoAccountDetail->fill([
                'coin' => $systemAccount->currency,
                'wallet_id' => $sharedWallet->wallet_id,
                'passphrase' => $sharedWallet->passphrase,
            ]);

            $operationService->transactionFromBitGoToExternal($cryptoAccountDetail, $externalAccount->cryptoAccountDetail, $transAmount, $operation, $transaction);
            $transaction->status = TransactionStatuses::SUCCESSFUL;
            $transaction->save();

            $operation->status = OperationStatuses::SUCCESSFUL;
            $operation->save();

            $operation->refresh();

            return $operation;
        } catch (\Throwable $throwable) {
            if (isset($operation)) {
                $operation->status = OperationStatuses::DECLINED;
                $operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                $operation->error_message = $throwable->getMessage();
                $operation->save();

                logger()->error('SystemFeeWithdrawOperationError', [
                    'operationId' => $operation->id,
                    'message' => $throwable->getMessage()
                ]);
            }

            throw $throwable;
        }
    }


    public function getExternalAccount(string $currency, string $address): Account
    {
        $externalAccount = Account::query()
            ->where('currency', $currency)
            ->where('is_external', true)
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->whereHas('cryptoAccountDetail', function ($q) use ($currency, $address) {
                $q->where([
                    'coin' => $currency,
                    'address' => $address
                ]);
            })->first();

        if (!$externalAccount) {
            $externalAccount = new Account();
            $externalAccount->fill([
                'account_type' => AccountType::TYPE_CRYPTO,
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM_CRYPTO_FEE,
                'status' => AccountStatuses::STATUS_ACTIVE,
                'name' => 'System Crypto Fee ' . $currency . ' account',
                'currency' => $currency,
                'is_external' => 1,
                'balance' => 0
            ]);
            $externalAccount->save();


            $cryptoAccountDetail = new CryptoAccountDetail();
            $cryptoAccountDetail->fill([
                'account_id' => $externalAccount->id,
                'coin' => $currency,
                'address' => $address,
                'label' => $address,
            ]);

            $cryptoAccountDetail->save();
        }

        return $externalAccount;
    }

    public function markTransactionsAsCollected(?array $collectedCryptoFeeIds, Operation $operation)
    {
        CollectedCryptoFee::query()->whereIn('id', $collectedCryptoFeeIds)->update([
            'is_collected' => 1,
            'operation_id' => $operation->id,
        ]);
    }

    public function getFeesForWithdraw(array $amounts, ?string $projectId = null): array
    {
        $currencies = $this->getNotCollectedTransactionsCurrencies();
        $feesForWithdraw = [];

        foreach ($currencies as $currency) {
            if (!isset($amounts[$currency])) {
                continue;
            }
            $feesForWithdraw[$currency] = $this->getFeesByCurrency($amounts[$currency], $currency, $projectId);
        }


        return $feesForWithdraw;
    }

    public function getFeesByCurrency(float $amount, string $currency, ?string $projectId = null)
    {
        /* @var CommissionsService $commissionService */
        $commissionService = resolve(CommissionsService::class);

        $providerAccount = Account::getProviderAccount($currency, Providers::PROVIDER_WALLET, null, null, $projectId);
        $fromCommission = $providerAccount->getAccountCommission(true);
        $feeAmount = $commissionService->calculateCommissionAmount($fromCommission, $amount);

        return [
            'feeAmount' => generalMoneyFormat($feeAmount, $currency),
            'blockchainFee' => $fromCommission->blockchain_fee,
        ];
    }
}

<?php

namespace App\Services;

use App\Facades\{ActivityLogFacade, EmailFacade};
use App\Operations\BuyCryptoFromFiat;
use App\Operations\OrderCardByCrypto;
use App\Operations\WithdrawCryptoPF;
use App\Services\Wallester\WallesterPaymentService;
use Illuminate\Http\Request;
use App\Enums\{AccountType,
    LogMessage,
    LogResult,
    LogType,
    OperationOperationType,
    OperationStatuses,
    OperationSubStatuses,
    PaymentFormTypes,
    TransactionStatuses,
    TransactionSteps,
    TransactionType};
use App\Models\{Account, MerchantWebhookAttempt, Operation, PaymentFormAttempt, Transaction};
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Profiler\Profile;


class TransactionService
{
    /**
     * @param int $type
     * @param float $transactionAmount
     * @param Account $fromAccount
     * @param Account $toAccount
     * @param $commitDate
     * @param int $status
     * @param float|null $exchangeRate
     * @param Operation $operation
     * @param null $fromCommissionId
     * @param null $toCommissionId
     * @param null $trxFrom
     * @param null $trxTo
     * @param null $step
     * @param null $recipientAmount
     * @return Transaction
     */
    public function createTransactions(
        int $type, float $transactionAmount, Account $fromAccount, Account $toAccount,
        $commitDate, int $status, ?float $exchangeRate, Operation $operation,
        $fromCommissionId = null, $toCommissionId = null, $trxFrom = null, $trxTo = null, $step = null, $recipientAmount = null, $parentTransaction = null
    )
    {
        $transaction = new Transaction([
            'id' => Str::uuid(),
            'type' => $type,
            'trans_amount' => $transactionAmount,
            'recipient_amount' => $recipientAmount ?? $transactionAmount,
            'from_account' => $fromAccount->id,
            'to_account' => $toAccount->id,
            'creation_date' => Carbon::now(),
            'transaction_due_date' => null,
            'commit_date' => $commitDate,
            'confirm_date' => null,
            'status' => $status,
            'exchange_rate' => $exchangeRate,
            'exchange_request_id' => null,
            'operation_id' => $operation->id,
            'parent_id' => $parentTransaction->id ?? null,
            'from_commission_id' => $fromCommissionId,
            'to_commission_id' => $toCommissionId,
        ]);

        $transaction->save();

        //change operation step
        if (!is_null($step)) {
            $operation->step = $step;
            $operation->save();
        }


        //update balance of accounts
        if ($transaction->status == TransactionStatuses::SUCCESSFUL) {
            $fromAccount->updateBalance();
            $toAccount->updateBalance();
        }

        ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESSFULLY, [
                'fromAccountType' => $trxFrom,
                'toAccountType' => $trxTo,
                'fromAccountName' => $fromAccount->name,
                'toAccountName' => $toAccount->name,
                'from_id' => $fromAccount->id,
                'to_id' => $toAccount->id
            ], LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED_SUCCESS, $operation->id, $operation->cProfile->cUser->id ?? null

        );

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    public function approveTransaction(Transaction $transaction)
    {
        $operation = $transaction->operation;
        $operationService = resolve(OperationService::class);
        /* @var OperationService $operationService*/
        $result = [];
        $success = false;
        if (in_array($operation->operation_type, [OperationOperationType::TYPE_TOP_UP_SEPA, OperationOperationType::TYPE_TOP_UP_SWIFT])) {
            switch ($operation->step) {
                case TransactionSteps::TRX_STEP_THREE:
                    try {
                        $result = $operationService->approveTopUpStepThree($transaction, $operation, $this);
                    } catch (\Exception $exception) {
                        logger()->error('TopUpWireException', [
                            'message' => $exception->getMessage(),
                        ]);
                        if (strpos($exception->getMessage(), OperationSubStatuses::getName(OperationSubStatuses::INSUFFICIENT_FUNDS)) !== false) {
                            $operation->substatus = OperationSubStatuses::INSUFFICIENT_FUNDS;
                        } else {
                            $operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                        }
                        $operation->error_message = $exception->getMessage();
                        $operation->save();
                        $result['message'] = $exception->getMessage();
                        break;
                    }
                    if (!empty($result['message']) && $result['message'] == 'Success') {
                        $success = true;
                        break;
                    }
                case TransactionSteps::TRX_STEP_FOUR:
                    $transaction->markAsSuccessful();
                    $operation->status = OperationStatuses::SUCCESSFUL;
                    $operation->save();
                    EmailFacade::sendSuccessfulExchangeCredittingSepaOrSwift($operation, $transaction->trans_amount);
                    $success = true;
                    break;
            }
        } elseif (in_array($operation->operation_type, [OperationOperationType::TYPE_CARD, OperationOperationType::MERCHANT_PAYMENT, OperationOperationType::TYPE_CARD_PF]) ) {
            $topUpCardService = resolve(TopUpCardService::class);
            /* @var TopUpCardService $topUpCardService*/
            switch ($operation->step) {

                // @todo check steps to be true

                case TransactionSteps::TRX_STEP_TWO:
                    $result = $success = $topUpCardService->approveTopUpCardTransaction($transaction);
                    break;

                case TransactionSteps::TRX_STEP_FOUR:
                    $result = $success = $topUpCardService->approveLiqToWalletTransaction($transaction);
                    break;

                case TransactionSteps::TRX_STEP_FIVE:
                    // wallet provider to client transaction approve
                    $result = $success = $topUpCardService->approveWalletToClientTransaction($transaction);
                    break;
            }
        } elseif (in_array($operation->operation_type, [OperationOperationType::TYPE_WITHDRAW_CRYPTO, OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF])) {
           $success = $this->approveWithdrawCryptoOperation($transaction);
        } elseif (in_array($operation->operation_type, [OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO, OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT])) {
            $transaction->markAsSuccessful();
            $success = true;
        } elseif ($operation->operation_type == OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO) {
            $wallesterPaymentService = resolve(WallesterPaymentService::class);
            /* @var WallesterPaymentService $wallesterPaymentService*/

            $result = $success = $wallesterPaymentService->approveClientToLiqProviderTransaction($transaction);
        } elseif (in_array($operation->operation_type,  [OperationOperationType::TYPE_TOP_UP_CRYPTO, OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF])) {
            $success = $this->approveTopUpCryptoOperation($transaction);
        } elseif ($operation->operation_type == OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT) {

            /* @var BuyCryptoFromFiatService $buyCryptoFromFiatService*/
            $buyCryptoFromFiatService = resolve(BuyCryptoFromFiatService::class);

            switch ($operation->step) {
                case TransactionSteps::TRX_STEP_FOUR:
                    $result = $success = $buyCryptoFromFiatService->approveLiqToWalletTransaction($transaction);
                    break;

                case TransactionSteps::TRX_STEP_FIVE:
                    $result = $success = $buyCryptoFromFiatService->approveWalletToClientTransaction($transaction);
                    break;
            }
        }
        return compact('result', 'success');

    }

    public function approveWithdrawCryptoOperation($transaction)
    {
        $operation = $transaction->operation;
        $transaction->markAsSuccessful();
        $operation->status = OperationStatuses::SUCCESSFUL;
        $operation->save();
        EmailFacade::sendSuccessfulWithdrawalOfCryptocurrencyToCryptoWallet($operation, formatMoney($transaction->trans_amount, $transaction->fromAccount->currency));

        if ($topUpCardPFOperation = $operation->parent) {
            if ($topUpCardPFOperation->paymentForm && $topUpCardPFOperation->paymentForm->type == PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM) {
                $this->makeTopUpCryptoExternalPF($operation, $transaction);
            }
        }
        return true;
    }

    public function makeTopUpCryptoExternalPF(Operation $operation, Transaction $lastCryptoTransaction)
    {
        $operationService = resolve(OperationService::class);
        /* @var OperationService $operationService */

        $accountService = resolve(AccountService::class);
        /* @var AccountService $accountService */

        $topUpCardPFOperation = $operation->parent;
        $cProfile = $topUpCardPFOperation->paymentForm->cProfile;
        $address = $operation->fromAccount->cryptoAccountDetail->address;

        $newCryptoAccount = $accountService->addWalletToClient($address, $operation->to_currency, $cProfile, true);


        $attempt = PaymentFormAttempt::query()->where([
            'payment_form_id' => $topUpCardPFOperation->paymentForm->id,
            'operation_id' => $topUpCardPFOperation->id,
        ])->latest()->first();

        $account = $attempt->recipientAccount;
        /* @var Account $account */

        $topUpCryptoExternalOperation = $operationService->createOperation(
            $cProfile->id,
            OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF,
            $lastCryptoTransaction->trans_amount,
            $operation->to_currency,
            $operation->to_currency,
            $newCryptoAccount->id,
            $account->id,
            OperationStatuses::SUCCESSFUL
        );

        $topUpCryptoExternalOperation->parent_id = $operation->id;
        $topUpCryptoExternalOperation->save();

        $tx = $this->createTransactions(
            TransactionType::CRYPTO_TRX, $lastCryptoTransaction->trans_amount,
            $newCryptoAccount, $account,
            date('Y-m-d H:i:s'), TransactionStatuses::SUCCESSFUL,
            null, $topUpCryptoExternalOperation,
            null, null,
            'external crypto wallet account', 'client wallet account',
            TransactionSteps::TRX_STEP_ONE
        );
        $tx->setTxId($lastCryptoTransaction->tx_id);

        $this->makeAttemptForWebhook($topUpCryptoExternalOperation);

    }

    public function approveTopUpCryptoOperation(Transaction $transaction)
    {
        $operation = $transaction->operation;
        $transaction->markAsSuccessful();
        if ($transaction->type == TransactionType::REFUND) {
            $operation->status = OperationStatuses::RETURNED;
            $operation->substatus = OperationSubStatuses::REFUND;
            $operation->save();
        } else {
            $operation->status = OperationStatuses::SUCCESSFUL;
            $operation->save();
            if ($operation->operation_type !== OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
                EmailFacade::sendSuccessfulIncomingCryptocurrencyPayment($operation);
            }
        }

        if ($operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
            $paymentForm = $operation->paymentForm;
            if ($paymentForm) {
                $profile = $paymentForm->cProfile;
                if ($profile->is_merchant && $profile->webhook_url) {
                    $this->makeAttemptForWebhook($operation);
                }
            }
            EmailFacade::sendC2CTransactionApprovedMessageToUser($transaction);
            EmailFacade::sendC2CTransactionApprovedMessageToMerchant($transaction);
        }

        if ($withdrawPFOperation = $operation->parent) {
            if ($topUpCardPFOperation = $withdrawPFOperation->parent) {
                if ($topUpCardPFOperation->paymentForm && $topUpCardPFOperation->paymentForm->type == PaymentFormTypes::TYPE_MERCHANT_INSIDE_FORM) {
                    $this->makeAttemptForWebhook($operation);
                }
            }
        }
        return true;
    }

    public function makeAttemptForWebhook(Operation $operation): MerchantWebhookAttempt
    {
        $merchant = $operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF ? $operation->paymentForm->cProfile : $operation->parent->parent->paymentForm->cProfile;

        $merchantWebhookAttempt = new MerchantWebhookAttempt();

        if ($merchant) {
            $merchantWebhookAttempt->fill([
                'webhook_url' => $merchant->webhook_url,
                'operation_id' => $operation->id,
                'merchant_id' => $merchant->id,
            ]);
            $merchantWebhookAttempt->save();
        }

        return $merchantWebhookAttempt;
    }


    public function handleApprovedTransaction(BitGOAPIService $bitGOAPIService, Transaction $transaction): bool
    {
        $fromAccount = $transaction->fromAccount;
        $toAccount = $transaction->toAccount;
        if (!$fromAccount->cryptoAccountDetail || !$toAccount->cryptoAccountDetail) {
            //TODO something went wrong and transaction didn't have crypto account details
            logger()->error('NoCryptoTransactionAccounts: ' . $transaction->id);
            return false;
        }

        $walletId = $fromAccount->cryptoAccountDetail->wallet_id ?: $toAccount->cryptoAccountDetail->wallet_id;

        try {
            $transfer = $bitGOAPIService->getTransfer($fromAccount->currency, $walletId, $transaction->tx_id);
        } catch (\Exception $exception) {
            logger()->error('TransferNotFound', ['message' => $exception->getMessage(), 'transaction' => $transaction->transaction_id]);
            return false;
        }

        $transactionIsApproved = $bitGOAPIService->isTransactionApproved($transfer);

        if ($transactionIsApproved) {
            logger()->info('ApproveTransactionAmountReceived', ['transaction' => $transaction->transaction_id, 'transfer' => $transfer]);
            $this->approveTransaction($transaction);
            return true;
        }
        return false;
    }

    public function getAccountTransactionsByIdPagination(Request $request, $accountId)
    {
        $query = Transaction::query();
        $query->where(function ($query) use ($accountId) {
            $query->where('to_account', $accountId)
                ->orWhere('from_account', $accountId);
        });
        if ($request->transaction_id) {
            $query->where('transaction_id', $request->transaction_id);
        }
        if ($request->profile_id) {
            $profile = (new CProfileService())->getCProfileByProfileId($request->profile_id);
            if ($profile) {
                $query->whereHas('operation', function ($q) use ($profile) {
                    $q->where('c_profile_id', $profile->id);
                })->with('operation');
            }
        }
        if ($request->from) {
            $query->where('updated_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->to) {
            $query->where('updated_at', '<=', $request->to . ' 23:59:59');
        }
        if ($request->status) {
            if ($request->status != -1) {
                $query->where('status', $request->status);
            }
        }

        if ($request->transaction_type) {
            $query->where('type', $request->transaction_type);
        }
        if ($request->amount) {
            $query->where('trans_amount', $request->amount);
        }
        return $query->orderBy('transaction_id', 'DESC')
            ->paginate(config('cratos.pagination.operation'));
    }

    public function getTransactionByTxIdAndOperationType($txId, $type)
    {
        return Transaction::query()
            ->where('tx_id', $txId)
            ->whereHas('operation', function ($q) use ($type) {
                return $q->where('operation_type', $type);
            })
            ->first();
    }
}

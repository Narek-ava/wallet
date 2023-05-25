<?php


namespace App\Services;


use App\DataObjects\CryptoTransferData;
use App\Facades\EmailFacade;
use App\Enums\{AccountType,
    Currency,
    OperationOperationType,
    OperationStatuses,
    TransactionStatuses,
    TransactionSteps,
    TransactionType};
use App\Models\{Account, CryptoAccountDetail, Operation, Transaction};
use Carbon\Carbon;
use Illuminate\Support\Str;

class CryptoAccountService
{
    public function createCryptoAccount($data)
    {
        $data['id'] = Str::uuid()->toString();
        CryptoAccountDetail::create($data);
    }

    public function createCryptoAccountDetail($account, $address, $riskScore): CryptoAccountDetail
    {
        $cryptoAccountDetail = new CryptoAccountDetail([
            'label' => $address,
            'account_id' => $account->id,
            'coin' => $account->currency,
            'address' => $address,
            'wallet_data' => json_encode([]),
            'verified_at' => is_null($riskScore) ? null : Carbon::now(),
            'risk_score' => $riskScore,
        ]);
        $cryptoAccountDetail->save();
        return $cryptoAccountDetail;
    }

    public function cryptoAccountCheck()
    {
        Account::getActiveClientCryptoAccounts()
            ->chunk(50, function($accounts) {
                foreach ($accounts as $account) {
                    $this->monitorAccountTransactions($account);
                }
        });
    }



    public function checkNewIncomingTrx(Account $account, string $address, string $txId, $baseValue, bool $isApproved)
    {
        $transaction = $account->incomingTransactions()->where(['tx_id' => $txId])->first();
        /* @var Transaction $transaction*/
        if (!$transaction) { //check if we didn't add transaction already
            $accountService = new AccountService();

            $newCryptoAccount = $accountService->addWalletToClient($address, $account->currency, $account->cProfile, true);

            if ($newCryptoAccount) {
                $operationService = new OperationService();
                $coinAmount = round($baseValue / Currency::BASE_CURRENCY[$account->currency], 8);

                $transactionService = new TransactionService();

                $otherAccountTransactionWithGivenTxId = $transactionService->getTransactionByTxIdAndOperationType($txId, OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF);

                $operationType = $otherAccountTransactionWithGivenTxId ? OperationOperationType::TYPE_TOP_UP_CRYPTO_PF : OperationOperationType::TYPE_TOP_UP_CRYPTO;
                $operation = $operationService->createOperation($account->cProfile->id, $operationType,
                    $coinAmount, $account->currency, $account->currency, $newCryptoAccount->id, $account->id);

                if ($otherAccountTransactionWithGivenTxId) {
                    $operation->parent_id = $otherAccountTransactionWithGivenTxId->operation->id;
                    $operation->save();
                }

                //create transaction 1 from external wallet to our client wallet
                $tx = $transactionService->createTransactions(
                    TransactionType::CRYPTO_TRX, $coinAmount,
                    $newCryptoAccount, $account,
                    date('Y-m-d H:i:s'), TransactionStatuses::PENDING,
                    null, $operation,
                    null, null,
                    'external crypto wallet account', 'client wallet account',
                    TransactionSteps::TRX_STEP_ONE
                );
                $tx->setTxId($txId);

                if ($operation->isLimitsVerified() && $newCryptoAccount->cryptoAccountDetail->isAllowedRisk()) {
                    if ($isApproved) {
                        $transactionService->approveTransaction($tx);
                    }
                    logger()->info('IncomingCryptoTransactionApproved', $tx->toArray());
                } else {
                    EmailFacade::sendNotificationForManager($operation->cProfile->cUser, $operation->operation_id);
                    logger()->info('IncomingCryptoTransactionPending', $tx->toArray());
                }

            }
        }

    }

    public function cryptoToCryptoPFIncomingTrx(Account $account, string $address, string $txId, Operation $operation, $baseValue, bool $isApproved)
    {
        $transaction = $account->incomingTransactions()->where(['tx_id' => $txId])->first();

        $accountService = new AccountService();

        $newCryptoAccount = $accountService->addWalletToClient($address, $account->currency, $account->cProfile, true);

        $operation->from_account = $newCryptoAccount->id;

        /* @var Transaction $transaction */
        if (!$transaction) {
            $paidAmount = round($baseValue / Currency::BASE_CURRENCY[$account->currency], 8);

            $operation->amount = $paidAmount;
            $operation->calculateAmountInEuro();
            $operation->save();

            /* @var TransactionService $transactionService */
            $transactionService = resolve(TransactionService::class);

            /* @var PaymentFormCryptoService $paymentFormCryptoService */
            $paymentFormCryptoService = resolve(PaymentFormCryptoService::class);

            $calculatedFee = $paymentFormCryptoService->calculateFee($operation->paymentForm, $paidAmount);

            $operation->paymentFormAttempt->incoming_fee = $operation->paymentForm->incoming_fee;
            $operation->paymentFormAttempt->save();

            //create transaction 1 from external wallet to our client wallet
            $tx = $transactionService->createTransactions(
                TransactionType::CRYPTO_TRX, $calculatedFee['leftAmount'],
                $operation->fromAccount, $account,
                date('Y-m-d H:i:s'), TransactionStatuses::PENDING,
                null, $operation,
                null, null,
                'external crypto wallet account', 'client wallet account',
                TransactionSteps::TRX_STEP_ONE
            );
            $tx->setTxId($txId);


            $systemAccount = Account::getSystemAccount($operation->to_currency, AccountType::TYPE_CRYPTO);
            $feeTrx = $transactionService->createTransactions(
                TransactionType::SYSTEM_FEE, $calculatedFee['feeAmount'],
                $tx->toAccount, $systemAccount,
                date('Y-m-d H:i:s'), TransactionStatuses::SUCCESSFUL,
                null, $operation,
                null, null,
                'client wallet account', 'system',
                TransactionSteps::TRX_STEP_ONE, null, $tx
            );

            /* @var CollectedCryptoFeeService $collectedCryptoFeeService */
            $collectedCryptoFeeService = resolve(CollectedCryptoFeeService::class);
            $collectedCryptoFeeService->saveCollectedCryptoFee($calculatedFee['feeAmount'], $tx->toAccount, $systemAccount, $feeTrx);

            EmailFacade::sendC2CTransactionDetectedMessageToUser($tx);
            EmailFacade::sendC2CTransactionDetectedMessageToMerchant($tx);

            if ($operation->isLimitsVerified() && $operation->fromAccount->cryptoAccountDetail->isAllowedRisk()) {
                if ($isApproved) {
                    $transactionService->approveTransaction($tx);
                }
                logger()->info('IncomingCryptoTransactionApproved', $tx->toArray());
            } else {
                EmailFacade::sendNotificationForManager($operation->cProfile->cUser, $operation->operation_id);
                logger()->info('IncomingCryptoTransactionPending', $tx->toArray());
            }
        }
    }

    /**
     * check if we have pending transaction, if yes than we approving transaction
     * @param Account $account
     * @param string $txId
     */
    public function checkWithdrawStatus(Account $account, string $txId)
    {
        //check if we have pending transaction
        $transaction = $account->outgoingTransactions()->where(['tx_id' => $txId])->first();
        /* @var Transaction $transaction*/
        if ($transaction && $transaction->status == TransactionStatuses::PENDING) {
            $transactionService = new TransactionService();
            $transactionService->approveTransaction($transaction);
        }
    }

    public function handleAccountTransfer(Account $account, CryptoTransferData $cryptoTransferData)
    {
        $isApproved = $cryptoTransferData->is_approved;
        if ($cryptoTransferData->is_received && $cryptoTransferData->value) {
            $this->checkNewIncomingTrx($account, $cryptoTransferData->from_address, $cryptoTransferData->tx_id, $cryptoTransferData->value, $isApproved);
        }
    }

    public function monitorAccountTransactions(Account $account)
    {
        $bitGOAPIService = new BitGOAPIService();
        $transfers = $bitGOAPIService->listTransfers($account->currency, $account->cryptoAccountDetail->wallet_id, $account->cryptoAccountDetail->address);

        if (!empty($transfers['transfers'])) {
            foreach ($transfers['transfers'] as $transfer) {
                $cryptoTransferData = $bitGOAPIService->getCryptoTransferData($transfer, $account->cryptoAccountDetail->address);
                $this->handleAccountTransfer($account, $cryptoTransferData);
            }
        }
    }
}

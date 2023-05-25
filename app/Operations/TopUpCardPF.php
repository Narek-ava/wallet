<?php

namespace App\Operations;

use App\DataObjects\OperationTransactionData;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\ComplianceLevel;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\PaymentFormTypes;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Models\Commission;
use App\Models\PaymentForm;
use App\Services\AccountService;
use App\Services\OperationService;
use App\Services\TransactionService;

class TopUpCardPF extends TopUpCard
{

    protected function sendFromWalletToClient()
    {
        $providerFeeAccount = $this->fromAccount->childAccount;
        if (!$providerFeeAccount) {
            throw new OperationException(t('transaction_message_provider_fee_account_failed'). ' L#226');
        }

        $blockChainFeeAmount = $this->fromAccount->cryptoBlockChainFee();

        $transactionAmount = $this->operationAmount - ($blockChainFeeAmount ?? 0);

        // transaction 1 from corporate wallet to corporate wallet fee;

        $fromCryptoAccount = $this->fromAccount->cryptoAccountDetail;
        $toCryptoAccount = $this->toAccount->cryptoAccountDetail;

        $transactionAmount = getCorrectAmount($transactionAmount, $this->fromAccount->currency);

        if ($toCryptoAccount->account->account_type != AccountType::TYPE_CRYPTO_FAKE) {
            $bitGoTransaction = $this->bitgoService->sendTransaction($fromCryptoAccount, $toCryptoAccount, $transactionAmount);
        }

        if (($toCryptoAccount->account->account_type != AccountType::TYPE_CRYPTO_FAKE && !empty($bitGoTransaction['transfer']['txid']))
            || $toCryptoAccount->account->account_type == AccountType::TYPE_CRYPTO_FAKE) {

            $this->_transaction = $this->_transactionService->createTransactions(
                TransactionType::CRYPTO_TRX, $transactionAmount, $this->fromAccount, $this->toAccount,
                $this->date, TransactionStatuses::PENDING,
                null, $this->_operation, $this->fromAccount->fromCommission->id, null,
                'Wallet provider', 'Client wallet',
            );
            if (!empty($bitGoTransaction)) {
                $this->_transaction->setTxId($bitGoTransaction['transfer']['txid']);
            }

            if ($blockChainFeeAmount) {
                $this->_transactionService->createTransactions(
                    TransactionType::SYSTEM_FEE, $blockChainFeeAmount, $this->fromAccount, $providerFeeAccount,
                    $this->date, TransactionStatuses::SUCCESSFUL, null, $this->_operation,
                    null, null, 'Wallet provider', 'Wallet provider fee', null, null, $this->_transaction
                );
            }
        }
        $this->_operation->step++;
        $this->_operation->save();

        if ($toCryptoAccount->account->account_type == AccountType::TYPE_CRYPTO_FAKE) {
            $transactionService = resolve(TransactionService::class);
            /* @var TransactionService $transactionService */
            $transactionService->approveTransaction($this->_transaction);
        }
    }



    public function makeWithdrawCryptoOperation($cProfile, $amount, $fromAccount)
    {
        $operationService = resolve(OperationService::class);
        /* @var OperationService $operationService*/
        $accountService = resolve(AccountService::class);
        /* @var AccountService $accountService*/

        $recipientAccount = $this->_operation->paymentFormAttempt->recipientAccount;
        $toAccount = $accountService->addWalletToClient($recipientAccount->cryptoAccountDetail->address, $recipientAccount->currency, $this->fromAccount->cProfile, true);

        //send from client to external
        $withdrawCryptoOperation = $operationService->createOperation(
            $cProfile->id,
            OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF,
            $amount,
            $fromAccount->currency,
            $toAccount->currency,
            $fromAccount->id,
            $toAccount->id
        );
        $withdrawCryptoOperation->parent_id = $this->_operation->id;
        $withdrawCryptoOperation->save();

        $complianceLevel = null;
        if ($this->_operation->operation_type == OperationOperationType::TYPE_CARD_PF) {
            if (in_array($this->_operation->paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
                $complianceLevel = $this->_operation->paymentForm->kyc ? ComplianceLevel::VERIFICATION_LEVEL_1 : ComplianceLevel::VERIFICATION_LEVEL_3;
            }
        }
        if ($withdrawCryptoOperation->isLimitsVerified($complianceLevel)) {

            $withdrawCryptoOperationData = new OperationTransactionData([
                'date' => date('Y-m-d'),
                'transaction_type' => TransactionType::CRYPTO_TRX,
                'from_type' => Providers::CLIENT,
                'to_type' => Providers::CLIENT,
                'from_currency' => $withdrawCryptoOperation->from_currency,
                'from_account' => $withdrawCryptoOperation->from_account,
                'to_account' => $withdrawCryptoOperation->to_account,
                'currency_amount' => $withdrawCryptoOperation->amount
            ]);
            try {
                $withdrawCryptoPF = new WithdrawCryptoPF($withdrawCryptoOperation, $withdrawCryptoOperationData);
                $withdrawCryptoPF->execute();
            } catch (\Exception $exception) {
                logger()->error('WithdrawByCryptoErrorWhileTopUpCardPF.' , [
                    'operationId' => $withdrawCryptoOperation->id,
                    'message' => $exception->getMessage()
                ]);
                return redirect()->route('cabinet.wallets.index')->with(['error' => t('withdraw_crypto_error_message')]);
            }
        }
    }



    public function getClientCommission(): Commission
    {
        $paymentForm = $this->_operation->paymentForm;
        if (!in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rateTemplateId = $paymentForm->cProfile->rate_template_id;
        } else {
            $rateTemplateId = $this->_operation->toAccount->cProfile->rate_template_id;
        }
        $clientCommission = $this->_commissionService->commissions(
            $rateTemplateId,
            CommissionType::TYPE_CARD,
            $this->_operation->from_currency,
            Commissions::TYPE_INCOMING
        );
        if (!$clientCommission) {
            throw new OperationException('Client Rates Missing Card');
        }

        return $clientCommission;
    }

    public function sendWithdrawCrypto()
    {
        $this->_transaction = $this->_operation->transactions()->where('type', TransactionType::CRYPTO_TRX)->latest()->first();

        if ($this->_transaction->status == TransactionStatuses::SUCCESSFUL && $this->_operation->status == OperationStatuses::SUCCESSFUL) {

            //send from client to external
            if ($this->_operation->paymentForm->type != PaymentFormTypes::TYPE_CLIENT_INSIDE_FORM) {
                $this->makeWithdrawCryptoOperation($this->_operation->cProfile, $this->operationAmount, $this->fromAccount);
            }
        }
    }


}

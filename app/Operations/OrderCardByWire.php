<?php


namespace App\Operations;

use App\DataObjects\OperationTransactionData;
use App\Enums\OperationStatuses;
use App\Enums\OperationSubStatuses;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionSteps;
use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Models\Account;
use App\Models\Commission;
use App\Models\Operation;
use App\Models\WallesterAccountDetail;
use App\Services\CollectedCryptoFeeService;
use App\Services\CommissionsService;
use App\Services\TransactionService;
use App\Services\Wallester\WallesterPaymentService;

class OrderCardByWire extends AbstractOperation
{
    protected function getSystemAccountType(): int
    {
        $paymentProvider = $this->getPaymentProviderAccount();
        return $paymentProvider->account_type;
    }

    protected function getPaymentProviderAccount(): Account
    {
        $providerAccount = $this->_operation->providerAccount;
        if (!$providerAccount || !$providerAccount->childAccount) {
            throw new OperationException("Provider fee account not found for operation {$this->_operation->id}");
        }
        return $providerAccount;
    }

    protected function getSystemAccount(): ?Account
    {
        $accountType = $this->getSystemAccountType();
        $systemAccount =  Account::getSystemAccount($this->_operation->from_currency, $accountType);
        if (!$systemAccount) {
            throw new OperationException('System Account Missing');
        }
        return $systemAccount;
    }

    public function getClientCommission(): Commission
    {
        return $this->fromAccount->getAccountCommission(false, TransactionType::BANK_TRX);
    }

    public function execute(): void
    {
        try {

            $request = $this->request;
            switch ($this->_operation->step) {
                case 0:
                    $isValid = ($request->transaction_type == TransactionType::BANK_TRX) &&
                        ($request->from_type == Providers::CLIENT) &&
                        ($request->to_type == Providers::PROVIDER_PAYMENT) &&
                        ($request->from_account == $this->_operation->from_account) &&
                        ($request->from_currency == $this->_operation->from_currency) && $request->to_account;

                    if (!$isValid) {
                        throw new OperationException(t('card_order_first_step_error'));
                    }
                    $this->addTransactionFromClientToPaymentProvider();
                    break;

                case 1:
                    $isValid = ($request->transaction_type == TransactionType::BANK_TRX) &&
                        ($request->from_type == Providers::PROVIDER_PAYMENT) &&
                        ($request->to_type == Providers::PROVIDER_LIQUIDITY) &&
                        ($request->from_currency == $this->_operation->from_currency);
                    if (!$isValid) {
                        throw new OperationException(t('withdraw_wire_first_transaction_valid'));
                    }
                    $this->addTransactionFromPaymentToLiq();
                    break;
            }
        } catch (\Exception $exception) {
            logger()->error('OrderByWireException', [
                'message' => $exception->getMessage(),
            ]);
            if (!($exception instanceof OperationException)) {
                if (strpos($exception->getMessage(), OperationSubStatuses::getName(OperationSubStatuses::INSUFFICIENT_FUNDS)) !== false) {
                    $this->_operation->substatus = OperationSubStatuses::INSUFFICIENT_FUNDS;
                } else {
                    $this->_operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                }
                $this->_operation->error_message = $exception->getMessage();
                $this->_operation->save();
            }

            throw new OperationException($exception->getMessage(), 0, $exception);
        }

    }

    protected function addTransactionFromClientToPaymentProvider()
    {
        $this->_operation->provider_account_id = $this->toAccount->id;

        $fromCommission = $this->getClientCommission();
        $toCommission = $this->toAccount->getAccountCommission(false, TransactionType::BANK_TRX);

        $systemAccount = $this->getSystemAccount();

        if (!$fromCommission || !$toCommission) {
            throw new OperationException(t('transaction_message_commissions_failed'));
        }

        if (!$systemAccount) {
            logger()->error('NoSystemAccount '. $this->_operation->id);
            throw new OperationException(t('transaction_message_system_account_failed'));
        }

        $providerFeeAccount = $this->toAccount->childAccount;
        if (!$providerFeeAccount) {
            throw new OperationException(t('transaction_message_provider_fee_account_failed'));
        }

        $transactionAmount = $this->_commissionService->calculateCommissionAmount($fromCommission, $this->operationAmount);


        //step 1 transaction 1 from client to valter
        $bankTransaction = $this->_transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->operationAmount,

            $this->fromAccount, $this->toAccount, $this->date, TransactionStatuses::SUCCESSFUL,
            null, $this->_operation, $fromCommission->id, $toCommission->id,
            'Client - ' . $this->fromAccount->name, 'Payment provider - ' . $this->toAccount->name, TransactionSteps::TRX_STEP_ONE
        );

        //step 1  transaction 2 from valter to system
        $this->_transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $this->toAccount, $systemAccount,
            $this->date , TransactionStatuses::SUCCESSFUL,
            null, $this->_operation,
            null, null,
            'Payment provider - ' . $this->toAccount->name, 'System - ' . $systemAccount->name, TransactionSteps::TRX_STEP_ONE, null, $bankTransaction
        );

        // step 1  transaction 3 from system to valter sepa fee
        $transactionAmount = $this->_commissionService->calculateCommissionAmount($toCommission, $this->operationAmount);

        $this->_transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $providerFeeAccount,
            $this->date, TransactionStatuses::SUCCESSFUL,
            null, $this->_operation,
            null, null,
            'System - ' . $systemAccount->name, 'Payment provider fee - ' . $providerFeeAccount->name, TransactionSteps::TRX_STEP_ONE, null, $bankTransaction
        );

        $this->_operation->step++;
        $this->_operation->received_amount = $this->operationAmount;
        $this->_operation->received_amount_currency = $bankTransaction->fromAccount->currency;

        $this->_operation->save();
    }

    protected function addTransactionFromPaymentToLiq()
    {
        $systemAccount = $this->_operation->getOperationSystemAccount();
        if (!$systemAccount) {
            throw new OperationException(t('transaction_message_system_account_failed'));
        }

        $fromCommission = $this->fromAccount->getAccountCommission(true, TransactionType::BANK_TRX);
        $toCommission = $this->toAccount->getAccountCommission(false, TransactionType::BANK_TRX);

        if (!$fromCommission || !$toCommission) {
            throw new OperationException(t('transaction_message_commissions_failed'));
        }

        $providerFeeAccount = $this->toAccount->providerFeeAccount;
        if (!$this->toAccount->childAccount || !$this->fromAccount->childAccount) {
            throw new OperationException(t('transaction_message_provider_fee_account_failed'). ' L#303');
        }

        $transactionAmount = $this->_commissionService->calculateCommissionAmount($fromCommission, $this->operationAmount);

        //transaction 1 from walter to kraken
        $bankTransaction = $this->_transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->operationAmount,
            $this->fromAccount, $this->toAccount, $this->date, TransactionStatuses::SUCCESSFUL,
            null, $this->_operation, $fromCommission->id, $toCommission->id,
            'Payment provider - ' . $this->fromAccount->name, 'Liquidity - ' . $this->toAccount->name, TransactionSteps::TRX_STEP_TWO
        );

        //transaction 2 from system fee to payment fee
        $this->_transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $this->fromAccount->providerFeeAccount, $this->date, TransactionStatuses::SUCCESSFUL,
            null, $this->_operation, null, null,
            'System fee - ' . $systemAccount->name, 'Payment provider fee - ' . $providerFeeAccount->name, TransactionSteps::TRX_STEP_TWO, null, $bankTransaction
        );

        //transaction 3 from system to liq provider fee fee
        $transactionAmount = $this->_commissionService->calculateCommissionAmount($toCommission, $this->operationAmount);
        $this->_transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $this->toAccount->providerFeeAccount, $this->date, TransactionStatuses::SUCCESSFUL,
            null, $this->_operation, null, null,
            'System - ' . $systemAccount->name, 'Liquidity provider fee - ' . $this->toAccount->name, TransactionSteps::TRX_STEP_TWO, null, $bankTransaction
        );
        $this->_operation->step++;
    }

}

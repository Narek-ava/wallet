<?php


namespace App\Operations;


use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Facades\EmailFacade;
use App\Models\Account;
use App\Models\Commission;
use App\Services\CommissionsService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\DB;

class WithdrawFromFiatWire extends AbstractOperation
{

    protected function getSystemAccountType(): int
    {
        return $this->_operation->toAccount->account_type;
    }

    protected function getSystemAccount(): ?Account
    {
        return $this->_operation->getOperationSystemAccount();
    }

    public function execute(): void
    {
        config()->set('projects.project', $this->_operation->cProfile->cUser->project);
        $request = $this->request;
        switch ($this->_operation->step) {
            case 0:
                $isValid = ($request->transaction_type == TransactionType::BANK_TRX) &&
                    ($request->from_type == Providers::CLIENT) &&
                    ($request->to_type == Providers::PROVIDER_PAYMENT) &&
                    ($request->from_account == $this->_operation->from_account) &&
                    ($request->from_currency == $this->_operation->from_currency) && $request->to_account;
                if (!$isValid) {
                    throw new OperationException( t('withdraw_wire_fiat_first_transaction_valid'));
                }
                if ($request->currency_amount != $this->_operation->amount) {
                    throw new OperationException(t('withdraw_wire_withdraw_amount_valid') . $this->_operation->amount . '!');
                }
                $this->_operation->step++;
                $this->setInitialFeeAmount(); // 1.1, 1.2
                $this->sendFromClientToSystem(); //1.3
                $this->sendFromClientToPayment();
                $this->feeIncomingFromSystemToPayment();

                $this->_operation->save();
                break;
            case 1:
                $isValid = ($request->transaction_type == TransactionType::BANK_TRX) &&
                    ($request->from_type == Providers::PROVIDER_PAYMENT) &&
                    ($request->to_type == Providers::CLIENT) &&
                    ($this->fromAccount->currency == $this->_operation->from_currency) &&
                    ($this->toAccount->currency == $this->_operation->to_currency);
                if (!$isValid) {
                    throw new OperationException(t('withdraw_wire_fiat_second_transaction_valid'));
                }
                $this->_operation->step++;
                $this->sendFromPaymentToClient();
                $this->feeOutgoingFromSystemToPayment();
                break;
        }
    }

    public function getClientCommission(): Commission
    {
        return $this->_operation->fromAccount->getAccountCommission(true, TransactionType::BANK_TRX, $this->_operation);
    }

    protected function feeIncomingFromSystemToPayment()
    {
        $providerAccount = $this->toAccount;
        $toCommission = $providerAccount->getAccountCommission(false);
        $this->providerFeeAmount = $this->_commissionService->calculateCommissionAmount($toCommission, $this->leftAmount);
        $transactionService = $this->_transactionService;
        $amount = getCorrectAmount($this->providerFeeAmount, $this->_operation->from_currency);
        $this->_systemFeeFromSystemToProvider = $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $amount, $this->_systemAccount, $providerAccount->childAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, null, $toCommission->id,
            'System', 'Payment provider fee', 1, $amount, $this->_transaction
        );
    }

    protected function sendFromPaymentToClient()
    {
        $fromCommission = $this->fromAccount->getAccountCommission(true);
        $transactionService = $this->_transactionService;
        $this->_transaction = $transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->operationAmount, $this->fromAccount, $this->toAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, $fromCommission->id, null,
            $this->_operation->step
        );

        EmailFacade::sendCompletedRequestForWithdrawalViaSepaOrSwift($this->_operation, $this->operationAmount);
    }

    protected function feeOutgoingFromSystemToPayment()
    {
        $systemAccount = $this->_systemAccount;
        $commission = $this->fromAccount->getAccountCommission(true);
        $commissionService = $this->_commissionService;
        $amount = $commissionService->calculateCommissionAmount($commission, $this->operationAmount);

        $transactionService = $this->_transactionService;
        $transactionService->createTransactions(TransactionType::SYSTEM_FEE, $amount, $systemAccount, $this->fromAccount->providerFeeAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, $commission->id, null,
            'System', 'Payment provider fee', $this->_operation->step, null, $this->_transaction);
    }

    protected function sendFromClientToPayment()
    {
        $transactionService = $this->_transactionService;
        $this->_transaction = $transactionService->createTransactions(TransactionType::BANK_TRX, $this->leftAmount, $this->fromAccount, $this->toAccount,
            $this->date, TransactionStatuses::SUCCESSFUL, null, $this->_operation, $this->getClientCommission()->id,
            $this->toAccount->getAccountCommission(false)->id, 'Client', 'Payment provider', $this->_operation->step);
        if ($this->_feeTransactionFromClientToSystem) {
            $this->_feeTransactionFromClientToSystem->parent_id = $this->_transaction->id;
            $this->_feeTransactionFromClientToSystem->save();
        }
    }
}

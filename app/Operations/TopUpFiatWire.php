<?php


namespace App\Operations;


use App\Enums\AccountType;
use App\Enums\OperationSubStatuses;
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

class TopUpFiatWire extends AbstractOperation
{

    protected function getSystemAccountType(): int
    {
        return AccountType::TYPE_FIAT;
    }

    protected function getSystemAccount(): ?Account
    {
        return $this->_operation->getOperationSystemAccount();
    }

    public function execute(): void
    {
        config()->set('projects.project', $this->_operation->cProfile->cUser->project);
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
                        throw new OperationException(t('top_up_fiat_wire_transaction_valid'));
                    }
                    $this->_operation->step++;

                    $this->setInitialFeeAmount();
                    $this->sendFromClientToPaymentProvider();
                    $this->feeFromPaymentProviderToSystem();
                    $this->feeIncomingFromSystemToPayment();

                    break;

                case 1:
                    $isValid = ($request->transaction_type == TransactionType::BANK_TRX) &&
                        ($request->from_type == Providers::PROVIDER_PAYMENT) &&
                        ($request->to_type == Providers::CLIENT) &&
                        ($this->fromAccount->currency == $this->_operation->from_currency) &&
                        ($this->toAccount->currency == $this->_operation->to_currency);
                    if (!$isValid) {
                        throw new OperationException(t('top_up_fiat_wire_second_transaction_valid') . ' '. $this->_operation->from_currency . t('withdraw_wire_to_liquidity') . $this->_operation->to_currency . '!');
                    }
                    $this->_operation->step++;
                    $this->sendFromPaymentProviderToClient();
                    $this->feeOutgoingFromSystemToPayment(); // 2.1, 2.2
                    // 2.1, 2.2
                    break;
            }
        } catch (\Exception $exception) {
            logger()->error('TopUpFiatWireException', [
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

    public function getClientCommission(): Commission
    {
        return $this->_operation->fromAccount->getAccountCommission(false, TransactionType::BANK_TRX, $this->_operation);
    }

    protected function sendFromClientToPaymentProvider()
    {
        $fromCommission = $this->fromAccount->getAccountCommission(false, TransactionType::BANK_TRX, $this->_operation);
        $transactionService = $this->_transactionService;
        $this->_transaction = $transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->operationAmount, $this->fromAccount, $this->toAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, $fromCommission->id, null,
            $this->_operation->step
        );
        $this->_operation->received_amount = $this->operationAmount;
        $this->_operation->received_amount_currency = $this->request->from_currency;
        $this->_operation->save();
    }

    protected function feeFromPaymentProviderToSystem()
    {
        $commission = $this->getClientCommission();
        $commissionService = $this->_commissionService;
        $amount = $commissionService->calculateCommissionAmount($commission, $this->operationAmount);
        $this->leftAmount = $this->operationAmount - $amount;
        $systemAccount = $this->_systemAccount;
        $transactionService = $this->_transactionService;
        $transactionService->createTransactions(TransactionType::SYSTEM_FEE, $amount, $this->toAccount, $systemAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, $commission->id, null,
            'Payment provider', 'System fee', $this->_operation->step, null, $this->_transaction);

    }

    protected function feeIncomingFromSystemToPayment()
    {
        $systemAccount = $this->_systemAccount;
        $commission = $this->toAccount->getAccountCommission(false);
        $commissionService = $this->_commissionService;
        $amount = $commissionService->calculateCommissionAmount($commission, $this->operationAmount);

        $transactionService = $this->_transactionService;
        $transactionService->createTransactions(TransactionType::SYSTEM_FEE, $amount, $systemAccount, $this->toAccount->providerFeeAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, null, $commission->id,
            'System', 'Payment provider fee', $this->_operation->step, null, $this->_transaction);

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


    protected function sendFromPaymentProviderToClient()
    {
        $fromCommission = $this->fromAccount->getAccountCommission(true);
        $transactionService = $this->_transactionService;
        $this->_transaction = $transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->operationAmount, $this->fromAccount, $this->toAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, $fromCommission->id, null,
            $this->_operation->step
        );
        $this->_operation->received_amount = $this->operationAmount;
        $this->_operation->received_amount_currency = $this->request->from_currency;
        $this->_operation->save();
    }

}

<?php


namespace App\Operations;


use App\Enums\{AccountType, OperationSubStatuses, TransactionStatuses, TransactionType};
use App\Services\{CommissionsService, TransactionService};
use App\Exceptions\OperationException;
use App\Models\{Account, Commission, Transaction};
use Illuminate\Support\Facades\DB;

class WithdrawCrypto extends AbstractOperation
{

    protected ?Transaction $_systemFeeFromSystemToProvider;
    protected ?Transaction $_blockchainFeeFromSystemToProvider;

    public function execute(): void
    {
        try {
            $this->setInitialFeeAmount();
            $this->sendFromClientToSystem();
            $this->sendFromSystemToProviderFee();
            $this->sendFromClientToExternal();
        }catch (\Exception $exception) {
            logger()->error('WithdrawCryptoException', [
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

    protected function sendFromClientToExternal()
    {
        $fromAccount = $this->_operation->fromAccount;
        $toAccount = $this->_operation->toAccount;
        $this->_transaction = $this->makeCryptoTransaction($fromAccount, $toAccount, getCorrectAmount($this->leftAmount, $this->_operation->from_currency), 'Client wallet', 'Client external wallet');
        if ($this->_feeTransactionFromClientToSystem) {
            $this->_feeTransactionFromClientToSystem->parent_id = $this->_transaction->id;
            $this->_feeTransactionFromClientToSystem->save();
        }
        if ($this->_systemFeeFromSystemToProvider) {
            $this->_systemFeeFromSystemToProvider->parent_id = $this->_transaction->id;
            $this->_systemFeeFromSystemToProvider->save();
        }
        if ($this->_blockchainFeeFromSystemToProvider) {
            $this->_blockchainFeeFromSystemToProvider->parent_id = $this->_transaction->id;
            $this->_blockchainFeeFromSystemToProvider->save();
        }
    }

    protected function getSystemAccountType(): int
    {
        return AccountType::TYPE_CRYPTO;
    }

    public function getClientCommission(): Commission
    {
        return $this->_operation->fromAccount->getAccountCommission(true);
    }

    protected function sendFromSystemToProviderFee()
    {
        $providerAccount = $this->getWalletProviderAccount();
        // @todo artak seperate blockchain transaction
        $toCommission = $providerAccount->getAccountCommission(true);
        $this->providerFeeAmount = (new CommissionsService())->calculateCommissionAmount($toCommission, $this->leftAmount);
//        $this->providerFeeAmount += $toCommission->blockchain_fee;
        $transactionService = new TransactionService();
        $this->_systemFeeFromSystemToProvider = $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, getCorrectAmount($this->providerFeeAmount, $this->_operation->from_currency), $this->_systemAccount, $providerAccount->childAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, null, $toCommission->id,
            'System', 'Wallet provider fee', 1
        );

        if ($toCommission->blockchain_fee) {
            $this->_blockchainFeeFromSystemToProvider = $transactionService->createTransactions(
                TransactionType::BLOCKCHAIN_FEE, getCorrectAmount($toCommission->blockchain_fee, $this->_operation->from_currency), $this->_systemAccount, $providerAccount->childAccount, $this->date,
                TransactionStatuses::SUCCESSFUL, null, $this->_operation, null, $toCommission->id,
                'System', 'Blockchain provider fee', 1
            );
        }
    }

    protected function getSystemAccount(): ?Account
    {
        return Account::getSystemAccount($this->_operation->from_currency, $this->getSystemAccountType());
    }
}

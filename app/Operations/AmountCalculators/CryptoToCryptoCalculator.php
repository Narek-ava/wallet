<?php


namespace App\Operations\AmountCalculators;


use App\Enums\OperationOperationType;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;

class CryptoToCryptoCalculator extends AbstractOperationCalculator
{


    protected function getAmountStepOne(): float
    {
        return $this->_operation->amount;
    }

    protected function getAmountStepTwo(): float
    {
        return 0;
    }

    protected function getAmountStepThree(): float
    {
        return 0;
    }

    protected function getAmountStepFour(): float
    {
        return 0;
    }

    public function getCardProviderFeeAmount(): float
    {
        return 0;
    }

    public function getPaymentProviderFeeAmount()
    {
        return 0;
    }


    protected function getLiquidityProviderFee(bool $from)
    {
        return 0;
    }

    public function getLiquidityProviderFeeAmountFiat(): float
    {
        return 0;
    }

    public function getLiquidityProviderFeeAmountCrypto(): float
    {
        return 0;
    }

    public function getWalletProviderFeeAmount(): float
    {
        $walletProviderAccount = $this->_operation->getTransactionByAccount(TransactionType::CRYPTO_TRX, TransactionStatuses::SUCCESSFUL)->toAccount ?? null;
        if(!($walletProviderAccount && $walletProviderAccount->childAccount)) {
            return 0;
        }
        $transaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE, TransactionStatuses::SUCCESSFUL, null, $walletProviderAccount->childAccount->id);

        return $transaction->trans_amount ?? 0;
    }

    public function getWalletProviderClientFeeAmount(): float
    {
        return $this->_operation->getAllTransactionsByProviderTypesQuery(true)->sum('trans_amount');
    }

    public function getCardProviderCratosFeeAmount(): float
    {
        return 0;
    }

    public function getLiquidityProviderCratosFeeAmountFiat(): float
    {
        return 0;
    }

    public function getLiquidityProviderCratosFeeAmountCrypto(): float
    {
        return 0;
    }

    public function getWalletProviderCratosFeeAmount(): float
    {
        $walletProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(true, Providers::PROVIDER_WALLET, Providers::PROVIDER_WALLET)->first();

        return $walletProviderFeeTransaction->trans_amount ?? 0;
    }

    public function getCratosFeeAmountFiat(): float
    {
        return 0;
    }

    public function getCratosFeeAmountCrypto(): float
    {
        if ($this->_operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
            return $this->_operation->getLastTransactionByType(TransactionType::SYSTEM_FEE)->trans_amount ?? 0;
        }

        return 0;
    }

    public function getClientFeeFiatAmount(): float
    {
        return 0;
    }

    public function getClientFeeCryptoAmount(): float
    {
        return $this->getWalletProviderCratosFeeAmount();
    }


    public function getClientFeeFiatPercentCommission()
    {
        return $this->_operation->getCardTransaction()->fromCommission->percent_commission ?? null;
    }

    public function getProviderCardProviderFeePercentCommission()
    {
        return null;
    }

    public function getProviderLiquidityFeeCryptoPercentCommission()
    {
        return null;
    }

    public function getProviderWalletFeePercentCommission()
    {
        $cryptoTransaction = $this->_operation->getTransactionByAccount(TransactionType::CRYPTO_TRX, TransactionStatuses::SUCCESSFUL, null, $this->_operation->to_account);
        if (!$cryptoTransaction) {
            return null;
        }
        $walletProviderAccount = $cryptoTransaction->fromAccount;

        return $walletProviderAccount->fromCommission->percent_commission ?? null;
    }

    public function getProviderPaymentProviderFeePercentCommission()
    {
        return null;
    }
}

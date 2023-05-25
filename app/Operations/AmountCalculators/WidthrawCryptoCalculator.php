<?php


namespace App\Operations\AmountCalculators;


use App\Enums\OperationOperationType;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;

class WidthrawCryptoCalculator extends AbstractOperationCalculator
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
        $exchangeTransaction = $this->_operation->getExchangeTransaction();

        return $exchangeTransaction->recipient_amount ?? 0;
    }

    protected function getAmountStepFour(): float
    {
        return 0;
    }

    public function getCardProviderFeeAmount(): float
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
        return $this->getLiquidityProviderFee(false);
    }

    public function getWalletProviderFeeAmount(): float
    {
        $walletProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(true, null,Providers::PROVIDER_WALLET)->first();

        if(!($walletProviderFeeTransaction)) {
            return 0;
        }

        $transactionBlockchain = $this->_operation->getTransactionByAccount(TransactionType::BLOCKCHAIN_FEE  );
        $amount = $walletProviderFeeTransaction->trans_amount ?? 0;
        $amount += $transactionBlockchain->trans_amount ?? 0;

        return $amount;
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
        $liquidityProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(true, Providers::PROVIDER_LIQUIDITY, Providers::PROVIDER_LIQUIDITY)->first();

        return $liquidityProviderFeeTransaction->trans_amount ?? 0;
    }

    public function getWalletProviderCratosFeeAmount(): float
    {

        if ($this->_operation->operation_type == OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF && isset($this->_operation->fromAccount)) {
            $fromAccount = $this->_operation->fromAccount;
            return $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE, TransactionStatuses::SUCCESSFUL, $fromAccount->id)->trans_amount ?? 0;
        }

        $walletProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(true, Providers::PROVIDER_WALLET)->first();

        return !empty($walletProviderFeeTransaction->trans_amount) ? $walletProviderFeeTransaction->trans_amount : 0;
    }

    public function getCratosFeeAmountFiat(): float
    {
        return 0;
    }

    public function getCratosFeeAmountCrypto(): float
    {
        return $this->getClientFeeCryptoAmount() - $this->getWalletProviderFeeAmount();
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

        return $cryptoTransaction->fromCommission->percent_commission ?? null;
    }

    public function getPaymentProviderFeeAmount()
    {
        return 0;
    }

    public function getProviderPaymentProviderFeePercentCommission()
    {
        return null;
    }

}

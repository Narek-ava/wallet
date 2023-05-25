<?php


namespace App\Operations\AmountCalculators;


use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;

class WidthrawFromFiatCalculator extends AbstractOperationCalculator
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
        $exchangeTransaction = $this->_operation->getExchangeTransaction();
        if (!$exchangeTransaction) {
            return 0;
        }
        $liquidityProviderAccount = $from ? $exchangeTransaction->fromAccount : $exchangeTransaction->toAccount;
        if (!($liquidityProviderAccount && $liquidityProviderAccount->childAccount)) {
            return 0;
        }

        if(!$from){
            $transaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE,
                TransactionStatuses::SUCCESSFUL, null, $liquidityProviderAccount->childAccount->id);

        } else {
            $transaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE,
                TransactionStatuses::SUCCESSFUL, null,  $exchangeTransaction->toAccount->childAccount->id );
        }

        return $transaction->trans_amount ?? 0;
    }

    public function getLiquidityProviderFeeAmountFiat(): float
    {
        $exchangeTransaction = $this->_operation->getExchangeTransaction();
        if (!$exchangeTransaction) {
            return 0;
        }

        return $this->_operation->getAllTransactionsByProviderTypesQuery(false, null, Providers::PROVIDER_LIQUIDITY)->sum('trans_amount');
    }

    public function getLiquidityProviderFeeAmountCrypto(): float
    {
        return 0;
    }

    public function getWalletProviderFeeAmount(): float
    {
        $walletProviderAccount = $this->_operation->getTransactionByAccount(TransactionType::CRYPTO_TRX)->toAccount ?? null;
        if(!($walletProviderAccount && $walletProviderAccount->childAccount)) {
            return 0;
        }

        $transactionSystemAmount = $this->_operation->getAllTransactionsByProviderTypesQuery(true)->sum('trans_amount');
        $transactionBlockchain = $this->_operation->getTransactionByAccount(TransactionType::BLOCKCHAIN_FEE );

        $transactionSystemAmount += $transactionBlockchain->trans_amount ?? 0;

        return $transactionSystemAmount;
    }

    public function getWalletProviderClientFeeAmount(): float
    {
        return $this->_operation->getAllTransactionsByProviderTypesQuery(true)->sum('trans_amount');
    }

    public function getCardProviderCratosFeeAmount(): float
    {
        $cardProviderAccount = $this->_operation->providerAccount;
        if (!$cardProviderAccount || !$cardProviderAccount->childAccount) {
            return 0;
        }

        $fromCardProviderToSystemFeeTransaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE, TransactionStatuses::SUCCESSFUL, $cardProviderAccount->id, null);
        $fromSystemToCardProviderFeeTransaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE, TransactionStatuses::SUCCESSFUL, null, $cardProviderAccount->childAccount->id);

       return ($fromCardProviderToSystemFeeTransaction && $fromSystemToCardProviderFeeTransaction) ? ($fromCardProviderToSystemFeeTransaction->trans_amount - $fromSystemToCardProviderFeeTransaction->trans_amount) : 0;
    }

    public function getLiquidityProviderCratosFeeAmountFiat(): float
    {
        $cardProviderAccount = $this->_operation->providerAccount;
        if (!$cardProviderAccount) {
            return 0;
        }

        $fromCardProviderToSystemFeeTransaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE, TransactionStatuses::SUCCESSFUL, $cardProviderAccount->id, null);
        $fromSystemToLiquidityProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(false, null, Providers::PROVIDER_LIQUIDITY)->first();

        return ($fromCardProviderToSystemFeeTransaction && $fromSystemToLiquidityProviderFeeTransaction) ? $fromCardProviderToSystemFeeTransaction->trans_amount - $fromSystemToLiquidityProviderFeeTransaction->trans_amount : 0;

    }

    public function getLiquidityProviderCratosFeeAmountCrypto(): float
    {
        $liquidityProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(true, Providers::PROVIDER_LIQUIDITY, Providers::PROVIDER_LIQUIDITY)->first();

        return $liquidityProviderFeeTransaction->trans_amount ?? 0;
    }

    public function getWalletProviderCratosFeeAmount(): float
    {
        $walletProviderFeeTransaction = $this->_operation->getAllTransactionsByProviderTypesQuery(true, Providers::PROVIDER_WALLET)->first();

        return $walletProviderFeeTransaction->trans_amount ?? 0;
    }

    public function getCratosFeeAmountFiat(): float
    {
        return $this->getClientFeeFiatAmount() - $this->getLiquidityProviderFeeAmountFiat() - $this->getPaymentProviderFeeAmount();
    }

    public function getCratosFeeAmountCrypto(): float
    {
        return 0;
    }

    public function getClientFeeFiatAmount(): float
    {
        $fromAccount = $this->_operation->fromAccount;
         if(!$fromAccount){
             return 0;
         }

        $transaction = $this->_operation->getTransactionByAccount(
            TransactionType::SYSTEM_FEE,
            TransactionStatuses::SUCCESSFUL,
            $fromAccount->id);

        return $transaction->trans_amount ?? 0;
    }

    public function getClientFeeCryptoAmount(): float
    {
        return $this->getWalletProviderFeeAmount();
    }


    public function getClientFeeFiatPercentCommission()
    {
        return $this->_operation->getPaymentTransaction()->fromCommission->percent_commission ?? null;
    }

    public function getProviderCardProviderFeePercentCommission()
    {
        return null;
     }

    public function getProviderLiquidityFeeCryptoPercentCommission()
    {
        $exchangeTransaction = $this->_operation->getExchangeTransaction();
        if (!$exchangeTransaction) {
            return 0;
        }

        $cryptoTransaction = $this->_operation->getTransactionByAccount(TransactionType::SYSTEM_FEE, TransactionStatuses::SUCCESSFUL, $exchangeTransaction->to_account, null);

       return $cryptoTransaction->fromCommission->percent_commission ?? null;
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

    public function getPaymentProviderFeeAmount()
    {
        return $this->_operation->getAllTransactionsByProviderTypesQuery(false, null, Providers::PROVIDER_PAYMENT)->sum('trans_amount');
    }

    public function getProviderPaymentProviderFeePercentCommission()
    {
        $cardProviderAccount = $this->_operation->providerAccount;
        if (!$cardProviderAccount) {
            return null;
        }

        return $cardProviderAccount->getAccountCommission(false)->percent_commission ?? null;
    }

}

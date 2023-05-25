<?php


namespace App\Operations;


use App\Enums\{Providers, TransactionStatuses, TransactionSteps, TransactionType};
use App\Exceptions\OperationException;
use App\Models\{Account, Commission};

class BuyCryptoFromFiat extends AbstractFiatToCryptoOperation
{

    protected function getSystemAccountType(): int
    {
        return $this->_operation->fromAccount->account_type;
    }

    protected function getSystemAccount(): ?Account
    {
        return $this->_operation->getOperationSystemAccount();
    }

    public function execute(): void
    {
        $request = $this->request;
        switch ($this->_operation->step) {
            case TransactionSteps::TRX_STEP_ONE:
                $isValid = $request->transaction_type == TransactionType::BANK_TRX &&
                    $request->from_type == Providers::CLIENT &&
                    $request->to_type == Providers::PROVIDER_LIQUIDITY &&
                    $request->from_account == $this->_operation->from_account &&
                    $request->from_currency == $this->_operation->from_currency && $request->to_account;
                if (!$isValid) {
                    throw new OperationException( t('buy_crypto_from_fiat_first_transaction_valid'));
                }
                if ($this->operationAmount != $this->_operation->amount) {
                    throw new OperationException('Invalid amount!');
                }
                $availableBalance = $this->_operation->fromAccount->getAvailableBalance() + $this->_operation->amount;
                if (!$availableBalance || floatval($availableBalance) < $this->_operation->amount) {
                    throw new OperationException(t('send_crypto_balance_fail'). ', '.$availableBalance. ' '. t('ui_available_balance_in_wallet'));
                }
                $this->_operation->step++;
                $this->setInitialFeeAmount();
                $this->sendFromClientToSystem();
                $this->sendFromClientToLiquidity();
                $this->feeIncomingFromSystemToLiquidity();

                $this->_operation->save();
                break;
            case TransactionSteps::TRX_STEP_TWO:
                $isValid = ($request->transaction_type == TransactionType::EXCHANGE_TRX) &&
                    ($request->from_type == Providers::PROVIDER_LIQUIDITY) &&
                    ($request->to_type == Providers::PROVIDER_LIQUIDITY) &&
                    ($this->fromAccount->currency == $this->_operation->from_currency) &&
                    ($this->toAccount->currency == $this->_operation->to_currency);
                if (!$isValid) {
                    throw new OperationException(t('withdraw_wire_second_transaction_valid') . $this->_operation->from_currency . t('withdraw_wire_to_liquidity') . $this->_operation->to_currency . '!');
                }
                $this->exchangeFromFiatToCrypto(); // 2.1, 2.2

                break;

            case TransactionSteps::TRX_STEP_THREE:
                $isValid = ($request->transaction_type == TransactionType::CRYPTO_TRX) &&
                    ($request->from_type == Providers::PROVIDER_LIQUIDITY) &&
                    ($request->to_type == Providers::PROVIDER_WALLET) &&
                    ($this->fromAccount->currency == $this->_operation->to_currency) &&
                    ($this->toAccount->currency == $this->_operation->to_currency);
                if (!$isValid) {
                    throw new OperationException(t('crypto_transaction_valid_liq_wallet'));
                }
                $this->sendFromLiquidityToWallet();
                break;


            case TransactionSteps::TRX_STEP_FOUR:
                $isValid = ($request->transaction_type == TransactionType::CRYPTO_TRX) &&
                    ($request->from_type == Providers::PROVIDER_WALLET) &&
                    ($request->to_type == Providers::CLIENT) &&
                    ($this->fromAccount->currency == $this->_operation->to_currency) &&
                    ($this->toAccount->currency == $this->_operation->to_currency);
                if (!$isValid) {
                    throw new OperationException(t('crypto_transaction_valid_wallet_client'));
                }
                $this->sendFromWalletToClient();
                break;
        }
    }

    public function getClientCommission(): Commission
    {
        return $this->_operation->fromAccount->getAccountCommission(true, TransactionType::BANK_TRX, $this->_operation);
    }

    protected function sendFromClientToLiquidity()
    {
        $this->_transaction = $this->_transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->leftAmount, $this->fromAccount, $this->toAccount,
            $this->date, TransactionStatuses::SUCCESSFUL, null, $this->_operation,
            null, null, 'Client Fiat Wallet', 'Liquidity fiat'
        );

        $this->_feeTransactionFromClientToSystem->parent_id = $this->_transaction->id;
        $this->_feeTransactionFromClientToSystem->save();

    }

    protected function feeIncomingFromSystemToLiquidity()
    {
        $providerAccount = $this->toAccount;
        $toCommission = $providerAccount->getAccountCommission(false);
        $this->providerFeeAmount = $this->_commissionService->calculateCommissionAmount($toCommission, $this->leftAmount);
        $amount = getCorrectAmount($this->providerFeeAmount, $this->_operation->from_currency);

        $this->_systemFeeFromSystemToProvider = $this->_transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $amount, $this->_systemAccount, $providerAccount->childAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, null, $toCommission->id,
            'System', 'Liquidity provider fee', null, $amount, $this->_transaction
        );
    }

}

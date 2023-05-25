<?php


namespace App\Operations;


use App\DataObjects\OperationTransactionData;
use App\Models\Operation;
use App\Enums\{TransactionStatuses, TransactionType};
use App\Exceptions\OperationException;

use App\Services\{BitGOAPIService, ExchangeInterface, KrakenService};

abstract class AbstractFiatToCryptoOperation extends AbstractOperation
{


    protected BitGOAPIService $_bitgoService;


    public function __construct(Operation $operation, OperationTransactionData $request)
    {
        $this->_bitgoService = new BitGOAPIService();
        parent::__construct($operation, $request);
    }

    protected function sendFromLiquidityToWallet()
    {

        if (!$this->fromAccount->childAccount) {
            throw new OperationException(t('transaction_message_liquidity_crypto_fee_account_failed'));
        }

        $exchangeService = resolve(ExchangeInterface::class);
        /* @var KrakenService $exchangeService*/
        $refId = $exchangeService->withdraw(
            $this->toAccount->currency,
            $this->toAccount->cryptoAccountDetail->label_in_kraken,
            $this->operationAmount
        );

        if (!$refId) {
            throw new OperationException('Failed Withdraw from Kraken to ' . $this->toAccount->cryptoAccountDetail->label_in_kraken);
        }


        if (config('app.env') == 'local') {
            $withdrawTransaction = [
                'amount' => $this->operationAmount,
                'fee' => $this->operationAmount / 100
            ];
        } else {
            sleep(5);
            $trxs = $exchangeService->withdrawStatus($this->toAccount->currency);

            if (!empty($trxs['result'])) {
                sleep(2);
                $withdrawTransaction = $exchangeService->getTransactionByRefId($trxs, $refId) ?? null;
                if (!$withdrawTransaction) {
                    throw new OperationException('Failed to check withdraw status from Kraken!');
                }
            }
        }

        $this->_transaction = $this->_transactionService->createTransactions(
            TransactionType::CRYPTO_TRX, $withdrawTransaction['amount'], $this->fromAccount, $this->toAccount,
            $this->date, TransactionStatuses::PENDING, null, $this->_operation,
            $this->fromAccount->from_commission_id, null, 'Liquidity provider', 'Wallet provider',
        );

        if (!empty($withdrawTransaction['fee'])) {
            // step 3 transaction 3  outgoing fee after withdraw (from liquidity crypto account to liquidity crypto fee account)
            $this->_transactionService->createTransactions(
                TransactionType::SYSTEM_FEE, $withdrawTransaction['fee'], $this->fromAccount, $this->fromAccount->childAccount,
                $this->date, TransactionStatuses::SUCCESSFUL, null, $this->_operation,
                $this->fromAccount->from_commission_id, null,
                'Liquidity provider', 'Liquidity provider fee', null, null, $this->_transaction
            );
        }

        $this->_transaction->setRefId($refId);
        $this->_operation->step++;
        $this->_operation->save();

    }

    /**
     * @throws \Exception
     */
    protected function exchangeFromFiatToCrypto()
    {

        if (!$this->fromAccount->childAccount) {
            throw new OperationException(t('transaction_message_provider_fee_failed'));
        }

        $liquidityCryptoAccount = $this->fromAccount->getLiquidityCryptoAccount($this->toAccount->currency);
        if (!$liquidityCryptoAccount) {
            throw new OperationException(t('transaction_message_liquidity_crypto_account_failed'));
        }

        $systemAccount = $this->getSystemAccount();
        $operation = $this->_operation;

        $exchangeService = resolve(ExchangeInterface::class);
        /* @var KrakenService $exchangeService*/
        $exchangeData = $exchangeService->executeExchange(
            $this->fromAccount->currency, $this->toAccount->currency, $this->operationAmount,  $operation->id
        );

        $transactionService = $this->_transactionService;

        //transaction 2 from Liquidity provider account to Liquidity crypto account
        $this->_transaction = $transactionService->createTransactions(
            TransactionType::EXCHANGE_TRX, $exchangeData->costAmount, $this->fromAccount, $liquidityCryptoAccount,
            $this->date, TransactionStatuses::SUCCESSFUL, $exchangeData->rateAmount, $operation,
            $exchangeData->fromCommission->id, null, 'Liquidity provider - ' . $this->fromAccount->name,
            'Liquidity provider - ' . $liquidityCryptoAccount->name, null, $exchangeData->transactionAmount
        );

        //transaction 1 from Liquidity provider account to Liquidity provider commission account
        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $exchangeData->feeAmount, $systemAccount, $this->fromAccount->childAccount,
            $this->date, TransactionStatuses::SUCCESSFUL, $exchangeData->rateAmount, $operation,
            $exchangeData->fromCommission->id, null, 'Liquidity provider', 'System', null, null, $this->_transaction
        );


        $operation->exchange_rate = $exchangeData->rateAmount;
        $operation->step++;
        $operation->save();
    }

    /**
     * @throws OperationException
     */
    protected function sendFromWalletToClient()
    {
        $providerFeeAccount = $this->fromAccount->childAccount;
        if (!$providerFeeAccount) {
            throw new OperationException(t('transaction_message_provider_fee_account_failed'));
        }

        $blockChainFeeAmount = $this->fromAccount->cryptoBlockChainFee();

        $transactionAmount = $this->operationAmount - ($blockChainFeeAmount ?? 0);

        // transaction 1 from corporate wallet to corporate wallet fee;

        $fromCryptoAccount = $this->fromAccount->cryptoAccountDetail;
        $toCryptoAccount = $this->toAccount->cryptoAccountDetail;

        $transactionAmount = getCorrectAmount($transactionAmount, $this->fromAccount->currency);
        $bitGoTransaction = $this->_bitgoService->sendTransaction($fromCryptoAccount, $toCryptoAccount, $transactionAmount);
        if (!empty($bitGoTransaction['transfer']['txid'])) {
            //transaction 2 from corporate wallet to client wallet

            $this->_transaction = $this->_transactionService->createTransactions(
                TransactionType::CRYPTO_TRX, $transactionAmount, $this->fromAccount, $this->toAccount,
                $this->date, TransactionStatuses::PENDING,
                null, $this->_operation, $this->fromAccount->fromCommission->id, null,
                'Wallet provider', 'Client wallet',
            );
            $this->_transaction->setTxId($bitGoTransaction['transfer']['txid']);

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
    }
}

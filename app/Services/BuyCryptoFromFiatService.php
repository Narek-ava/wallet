<?php


namespace App\Services;



use App\DataObjects\OperationTransactionData;
use App\Enums\OperationStatuses;
use App\Enums\Providers;
use App\Enums\TransactionSteps;
use App\Enums\TransactionType;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Models\Operation;
use App\Models\Transaction;
use App\Operations\AbstractOperation;
use App\Operations\BuyCryptoFromFiat;

class BuyCryptoFromFiatService
{

    public function getSystemFeeTransaction(Operation $operation): ?Transaction
    {
        return $operation->transactions()->where([
            'type' => TransactionType::SYSTEM_FEE,
            'from_account' => $operation->from_account
        ])->first();
    }


    public function getExchangeTransaction(Operation $operation): ?Transaction
    {
        return $operation->transactions()->where([
            'type' => TransactionType::EXCHANGE_TRX,
        ])->first();
    }

    public function getLiqToWalletTransaction(Operation $operation): ?Transaction
    {
        return $operation->transactions()->where([
            'type' => TransactionType::CRYPTO_TRX,
        ])->first(); //@todo fiat use account provider types
    }

    public function getAllowedAmountForOperationStep(Operation $operation)
    {
        switch ($operation->step) {
            case TransactionSteps::TRX_STEP_TWO:
                $feeTransaction = $this->getSystemFeeTransaction($operation);
                return $operation->amount - ($feeTransaction->trans_amount ?? 0);
                break;
            case TransactionSteps::TRX_STEP_THREE:
                $exchangeTransaction = $this->getExchangeTransaction($operation);
                return getCorrectAmount($exchangeTransaction->recipient_amount, $operation->to_currency);
                break;

            case TransactionSteps::TRX_STEP_FOUR:
                $liqToWalletTransaction = $this->getLiqToWalletTransaction($operation);
                return getCorrectAmount($liqToWalletTransaction->recipient_amount ?? 0, $operation->to_currency);
                break;
            default:
                $operation->amount;
                break;
        }
    }

    public function approveLiqToWalletTransaction(Transaction $transaction): bool
    {
        $transaction->markAsSuccessful();
        $operation = $transaction->operation;

        $transactionData = new OperationTransactionData([
            'transaction_type' => TransactionType::CRYPTO_TRX,
            'from_type' => Providers::PROVIDER_WALLET,
            'to_type' => Providers::CLIENT,
            'from_currency' => $transaction->operation->to_currency,
            'from_account' => $transaction->toAccount->id,
            'to_account' => $operation->toAccount->id,
            'currency_amount' => $transaction->trans_amount
        ]);

        $buyCryptoFromFiat = new BuyCryptoFromFiat($operation, $transactionData);
        $buyCryptoFromFiat->execute();

        return true;
    }

    public function approveWalletToClientTransaction(Transaction $transaction): bool
    {
        $transaction->markAsSuccessful();
        $operation = $transaction->operation;
        $operation->status = OperationStatuses::SUCCESSFUL;
        $operation->save();
        EmailFacade::sendSuccessfulTopUpCardOperationMessage($operation, $transaction->trans_amount);

        //ActivityLogFacade::saveLog(LogMessage::CARD_OPERATION_SUCCESS, ['operationNumber' => $operation->operation_id], LogResult::RESULT_SUCCESS, LogType::TYPE_CARD_OPERATION_SUCCESS, null, $operation->cProfile->cUser->id);

        $transactionData = new OperationTransactionData([
            'transaction_type' => TransactionType::CRYPTO_TRX,
            'from_type' => Providers::PROVIDER_WALLET,
            'to_type' => Providers::CLIENT,
            'from_currency' => $transaction->operation->to_currency,
            'from_account' => $transaction->toAccount->id,
            'to_account' => $operation->toAccount->id,
            'currency_amount' => $transaction->trans_amount
        ]);

        $buyCryptoFromFiat = new BuyCryptoFromFiat($operation, $transactionData);
        $buyCryptoFromFiat->execute();

        return true;
    }

}

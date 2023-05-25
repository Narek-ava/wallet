<?php


namespace App\Services;


use App\Enums\OperationOperationType;
use App\Enums\TransactionStatuses;
use App\Models\{Account, CryptoAccountDetail, CryptoWebhook, Transaction};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class CryptoWebhookService
{


    public function addToQueue(?string $cryptoAccountDetailId, array $payload, ?string $wallet_id = null): bool
    {
        $cryptoWebhook = new CryptoWebhook();
        $cryptoWebhook->crypto_account_detail_id = $cryptoAccountDetailId;
        $cryptoWebhook->payload = $payload;
        $cryptoWebhook->wallet_id = $wallet_id;
        return $cryptoWebhook->save();
    }


    public function runQueues()
    {
        $cryptoWebhooks = CryptoWebhook::query()
            ->where(['status' => CryptoWebhook::STATUS_PENDING])
            ->orWhere(function(Builder $q) {
               $q->where(['status' => CryptoWebhook::STATUS_ERROR])
                   ->where('failed_count', '<',  5)
               ->where('updated_at', '<', date('Y-m-d H:i:s', strtotime('-5 min')));
            })->get();

        foreach ($cryptoWebhooks as $cryptoWebhook) {
            $this->handleQueue($cryptoWebhook);
        }
    }


    public function handleQueue(CryptoWebhook $cryptoWebhook)
    {
        if ($cryptoWebhook->crypto_account_detail_id) {
            $cryptoAccountDetail = $cryptoWebhook->cryptoAccountDetail;
            if (!$cryptoAccountDetail) {
                logger()->error('CryptoWebhookDeletedAccount', $cryptoWebhook->toArray());
                $cryptoWebhook->status = CryptoWebhook::STATUS_ERROR;
                $cryptoWebhook->failed_count++;
                $cryptoWebhook->save();
                return false;
            }
            $cryptoAccountDetail->webhook_received_at = Carbon::now();
            $cryptoAccountDetail->save();
        }


        /* @var BitGOAPIService $bitGOAPIService*/
        $bitGOAPIService = resolve(BitGOAPIService::class);

        /* @var TransactionService $transactionService*/
        $transactionService = resolve(TransactionService::class);

        /* @var CryptoAccountService $cryptoAccountService*/
        $cryptoAccountService = resolve(CryptoAccountService::class);

        $transfer = $cryptoWebhook->payload;

        $transactions = Transaction::query()->where(['tx_id' => $transfer['hash']])->get();


        $cryptoWebhook->status = CryptoWebhook::STATUS_SUCCESS;



        foreach ($transactions as $transaction) {
            /* @var Transaction $transaction*/
            logger()->debug('CryptoWebhookTransactionFound', $transaction->toArray());
            if ($bitGOAPIService->isTransactionApproved($transfer)) {
                if ($transaction->status == TransactionStatuses::PENDING) {
                    if ($transaction->operation->operation_type != OperationOperationType::TYPE_TOP_UP_CRYPTO
                        || ($transaction->operation->isLimitsVerified()
                            && $transaction->fromAccount->cryptoAccountDetail->isAllowedRisk())
                    ) {
                        $transactionService->handleApprovedTransaction($bitGOAPIService, $transaction);
                    }
                } else {
                    logger()->debug('CryptoWebhookTransactionSkip', $transaction->toArray());
                }
            }
            $cryptoWebhook->status = CryptoWebhook::STATUS_SUCCESS;
        }


        if (!empty($cryptoAccountDetail) && $cryptoAccountDetail->account->cProfile) {
            logger()->debug('CryptoWebhookCprofile', $cryptoAccountDetail->account->toArray());
            $cryptoAccountService->monitorAccountTransactions($cryptoAccountDetail->account);
            $cryptoWebhook->status = CryptoWebhook::STATUS_SUCCESS;
        } elseif ($cryptoWebhook->wallet_id) {
            $payload = $cryptoWebhook->payload;
            $transfer = $bitGOAPIService->getTransfer($payload['coin'], $cryptoWebhook->wallet_id, $payload['hash']);
            if ($transfer) {
                try {
                    $cryptoTransferData = $bitGOAPIService->getCryptoTransferData($transfer);
                } catch (\Exception $exception) {
                    return false;
                }
                if ($cryptoTransferData->is_received) {
                    $account = Account::getActiveClientCryptoAccounts()->whereHas('cryptoAccountDetail', function ($q) use($cryptoTransferData) {
                        $q->where(['address' => $cryptoTransferData->to_address]);
                    })->first();

                    /* @var Account $account*/
                    if ($account) {
                        $cryptoAccountService->handleAccountTransfer($account, $cryptoTransferData);
                    }
                }
            }
        }
        if ($cryptoWebhook->status != CryptoWebhook::STATUS_SUCCESS) {
            $cryptoWebhook->status = CryptoWebhook::STATUS_ERROR;
            $cryptoWebhook->failed_count++;
            logger()->debug('CryptoWebhookFailed', $transfer);
        }


        return $cryptoWebhook->save();
    }

}

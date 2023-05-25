<?php

namespace App\Services;

use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Models\MerchantWebhookAttempt;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Response;
use Psr\Http\Message\ResponseInterface;

class WebhookService
{
    public function sendMerchantCardPaymentWebhook(MerchantWebhookAttempt $merchantWebhookAttempt)
    {
        try {
            $secretKey = $merchantWebhookAttempt->cProfile->getSecretKey();

            $sendData = $this->getMerchantPaymentFormOperationInfo($merchantWebhookAttempt);
            $this->sendRequest($merchantWebhookAttempt->webhook_url, $sendData, $secretKey);

            $merchantWebhookAttempt->increment('attempts');
            $merchantWebhookAttempt->status = MerchantWebhookAttempt::STATUS_SUCCESS;
            $merchantWebhookAttempt->save();
        } catch (ClientException $clientException) {
            $merchantWebhookAttempt->status =  $merchantWebhookAttempt->attempts >= MerchantWebhookAttempt::MAX_ATTEMPTS ? MerchantWebhookAttempt::STATUS_FAILED : MerchantWebhookAttempt::STATUS_PENDING;
            $merchantWebhookAttempt->error_message = $clientException->getMessage();
            $merchantWebhookAttempt->save();
        }

    }

    public function getMerchantPaymentFormOperationInfo(MerchantWebhookAttempt $merchantWebhookAttempt)
    {
        $operation = $merchantWebhookAttempt->operation;

        $operationData = [
            'ID' => $merchantWebhookAttempt->id,
            'operationId' => $operation->id,
            'operationNumber' => $operation->operation_id,
            'operationType' => OperationOperationType::getName($operation->operation_type),
            'status' => OperationStatuses::getName($operation->status),
            'fromCurrency' => $operation->from_currency,
            'amount' => $operation->amount,
            'toCurrency' => $operation->to_currency,
        ];

        if ($operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
            $individualProfile = $operation->paymentFormAttempt->cProfile;

            $operationData = array_merge($operationData, [
                'topUpFee' => $operation->paymentFormAttempt->incoming_fee ?? 0,
            ]);

        } elseif (in_array($operation->operation_type, [OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF, OperationOperationType::TYPE_TOP_UP_CRYPTO_PF])) {
            $topUpCardPFOperation = $operation->parent->parent ?? null;

            if (!$topUpCardPFOperation) {
                return [];
            }

            $individualProfile = $topUpCardPFOperation->cProfile;

            $operationData = array_merge($operationData, [
                'cardNumberMask' => $topUpCardPFOperation->fromAccount->cardAccountDetail->card_number ?? '',
                'transactionReference' => $topUpCardPFOperation->getCardTransactionReference(),
                'blockchainFee' => $topUpCardPFOperation->getCardTransferBlockchainFee(),
                'topUpFee' => $operation->top_up_fee ?? 0,
                'exchangeRate' => $topUpCardPFOperation->getExchangeTransaction() ? round($operation->getExchangeTransaction()->exchange_rate, 2) : '',
            ]);
        }

        $operationData = array_merge($operationData, [
            'credited' => $operation->credited,
            'date' => $operation->created_at->toDateTimeString(),
            'payerName' => $individualProfile->getFullName() ?? '',
            'payerPhoneNumber' => $individualProfile->cUser->phone ?? '',
            'payerEmail' => $individualProfile->cUser->email ?? '',
        ]);

        return $operationData;
    }

    public function createSignature(array $sendData, string $secretKey): string
    {
        return hash_hmac('sha1', json_encode($sendData), $secretKey);
    }

    public function sendRequest(string $url, array $sendData,string $secretKey): ResponseInterface
    {
        return (new Client())->request('POST', $url, [
            'headers' => [
                'x-payload-digest' => $this->createSignature($sendData, $secretKey),
            ],
            'body' => json_encode($sendData)
        ]);
    }


}

<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Models\Operation;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Operation $resource
 */
class TopUpCryptoPF extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $topUpCardPFOperation = $this->resource->parent->parent ?? null;
        if (!$topUpCardPFOperation) {
            return [];
        }

        $individualProfile = $topUpCardPFOperation->cProfile;

        return [
            'operationId' => $this->resource->id,
            'operationNumber' => $this->resource->operation_id,
            'operationType' => OperationOperationType::getName($this->resource->operation_type),
            'fromCurrency' => $this->resource->from_currency,
            'toCurrency' => $this->resource->to_currency,
            'amount' => $this->resource->amount,
            'status' => OperationStatuses::getName($this->resource->status),
            'date' => $this->resource->created_at->toDateTimeString(),
            'cardPaymentDetails' => [
                'cardNumberMask' => $topUpCardPFOperation->fromAccount->cardAccountDetail->card_number ?? '',
                'blockchainFee' => $topUpCardPFOperation->getCardTransferBlockchainFee(),
                'topUpFee' => $topUpCardPFOperation->top_up_fee ?? '',
                'transactionReference' => $topUpCardPFOperation->getCardTransactionReference(),
                'exchangeRate' => $topUpCardPFOperation->getExchangeTransaction() ? round($topUpCardPFOperation->getExchangeTransaction()->exchange_rate, 2) : '',
                'payerName' => $individualProfile->getFullName() ?? '',
                'payerPhoneNumber' => $individualProfile->cUser->phone ?? '',
                'payerEmail' => $individualProfile->cUser->email ?? '',
            ],
        ];
    }
}

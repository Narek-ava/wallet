<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Operation;

/**
 * @property Operation $resource
 */
class CardOperationResource extends OperationResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dataArray = [
            'transactionID' => $this->resource->getCardTransactionReference()  ?? '',
            'blockchainFee' => $this->resource->getCardTransferBlockchainFee() ?? 0,
            'topUpFee' => $this->resource->top_up_fee ?? 0,
            'exchangeRate' => $this->resource->getExchangeTransaction() ? round($this->resource->getExchangeTransaction()->exchange_rate, 2) : 0.00,
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

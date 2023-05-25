<?php

namespace App\Http\Resources\Cabinet\API\v1;


use App\Models\Country;
use App\Models\Operation;

/**
 * @property Operation $resource
 */
class WithdrawWireOperationResource extends OperationResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $commission = $this->resource->calculateFeeCommissions();
        $dataArray = [
            'withdrawalMethod' => $this->resource->getOperationMethodName(),
            'withdrawalFee' => $commission->percent_commission ?? 0,
            'blockchainFee' => formatMoney($this->resource->blockchainFee, $this->resource->from_currency) ?? 0,
            'country' => $this->resource->toAccount ? Country::getCountryNameByCode($this->resource->toAccount->country) : '',
            'exchangeRate' => $this->resource->getExchangeTransaction() ? round($this->resource->getExchangeTransaction()->exchange_rate, 2) : 0.00,
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

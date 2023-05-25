<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Country;
use App\Models\Operation;

/**
 * @property Operation $resource
 */
class TopUpWireOperationResource extends OperationResource
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
            'topUpMethod' => $this->resource->getOperationMethodName(),
            'bankCountry' => $this->resource->fromAccount ? Country::getCountryNameByCode($this->resource->fromAccount->country) : '',
            'topUpFee' => $this->resource->topUpFee,
            'exchangeRate' => $this->resource->getExchangeTransaction() ? round($this->resource->getExchangeTransaction()->exchange_rate, 2) : 0.00,
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

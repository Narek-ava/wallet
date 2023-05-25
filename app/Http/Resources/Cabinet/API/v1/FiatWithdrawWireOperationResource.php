<?php

namespace App\Http\Resources\Cabinet\API\v1;


use App\Models\Country;
use App\Models\Operation;

/**
 * @property Operation $resource
 */
class FiatWithdrawWireOperationResource extends OperationResource
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
            'country' => $this->resource->toAccount ? Country::getCountryNameByCode($this->resource->toAccount->country) : '',
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

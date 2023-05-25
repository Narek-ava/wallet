<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Operation;

/**
 * @property Operation $resource
 */
class TopUpCryptoOperationResource extends OperationResource
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
            'fromWallet' => $this->resource->fromAccount->cryptoAccountDetail->address ?? '',
            'topUpFee' => $this->resource->crypto_fee,
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

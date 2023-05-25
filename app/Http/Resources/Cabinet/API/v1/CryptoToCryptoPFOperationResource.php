<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Operation;


/* @property Operation $resource */

class CryptoToCryptoPFOperationResource extends OperationResource
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
            'topUpFee' => $this->resource->paymentFormAttempt->incoming_fee ?? 0,
            'payerName' => $this->resource->paymentFormAttempt->getPayerFullName() ?? 0,
            'payerPhone' => $this->resource->paymentFormAttempt->phone ?? '',
            'payerEmail' => $this->resource->paymentFormAttempt->email ?? '',
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

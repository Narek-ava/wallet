<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\OperationSubStatuses;
use App\Models\Operation;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @property Operation $resource
 */
class OperationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'operationId' => $this->resource->id,
            'operationNumber' => $this->resource->operation_id,
            'operationType' => OperationOperationType::getName($this->resource->operation_type),
            'amount' => $this->resource->amount,
            'amountInEuro' => floatval(generalMoneyFormat($this->resource->amount_in_euro, Currency::CURRENCY_EUR)),
            'credited' => $this->resource->credited,
            'fromCurrency' => $this->resource->from_currency,
            'toCurrency' => $this->resource->to_currency,
            'fromAccount' => $this->resource->fromAccount->name ?? '',
            'toAccount' => $this->resource->toAccount->name ?? '',
            'status' => OperationStatuses::getName($this->resource->status),
            'date' => $this->resource->created_at->toDateTimeString(),
            'transactionExplorerUrl' => $this->resource->getCryptoExplorerUrl() ?? '',
        ];
    }
}

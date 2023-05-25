<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\AccountType;
use App\Models\WireAccountDetail;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property WireAccountDetail $resource
 */
class WireAccountDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'accountHolder' => $this->resource->account_beneficiary,
            'accountNumber' => $this->resource->account_number,
            'beneficiaryAddress' => $this->resource->beneficiary_address ?? '',
            'type' => $this->resource->account && in_array($this->resource->account->account_type, array_keys(AccountType::ACCOUNT_WIRE_TYPES)) ? AccountType::ACCOUNT_WIRE_TYPES[$this->resource->account->account_type] : '',
            'iban' => $this->resource->iban,
            'swift' => $this->resource->swift,
            'bankName' => $this->resource->bank_name,
            'bankAddress' => $this->resource->bank_address,
            'correspondentBank' => $this->resource->correspondent_bank ?? '',
            'correspondentBankSwift' => $this->resource->correspondent_bank_swift ?? '',
            'intermediaryBank' => $this->resource->intermediary_bank ?? '',
            'intermediaryBankSwift' => $this->resource->intermediary_bank_swift ?? '',
        ];
    }
}

<?php

namespace App\Http\Resources\Cabinet\API\v1;


use App\Enums\CommissionType;
use App\Enums\OperationOperationType;
use App\Models\Operation;
use App\Services\CommissionsService;

/**
 * @property Operation $resource
 */
class WithdrawCryptoOperationResource extends OperationResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $commissions = $this->resource->getWithdrawCryptoCommissions();
        $topUpCardPFOperation = $this->resource->parent;

        $dataArray = [
            'walletVerified' => $this->resource->is_verified ? 'Yes' : 'No',
            'blockchainFee' => isset($commissions->blockchain_fee) ? $commissions->blockchain_fee * OperationOperationType::BLOCKCHAIN_FEE_COUNT_WITHDRAW_CRYPTO : 0,
            'withdrawalFee' => $commissions->percent_commission ?? 0,
            'walletServiceFee' => 0,
            'parentId' => $topUpCardPFOperation->id ?? "",
            'parentOperationType' => $topUpCardPFOperation ? OperationOperationType::getName($topUpCardPFOperation->operation_type) : "",
        ];

        return array_merge(parent::toArray($request), $dataArray);
    }
}

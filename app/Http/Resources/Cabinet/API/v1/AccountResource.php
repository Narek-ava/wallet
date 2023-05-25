<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\AccountStatuses;
use App\Models\Account;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/**
 * @property Account $resource
 */
class AccountResource extends JsonResource
{
    protected $withWireDetails = false;
    protected $withAnyRelation = true;
    protected $walletBlockedStatus = false;

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

        $dataArray = [
            'id' => $this->resource->id,
            'accountId' => $this->resource->account_id,
            'status' => $this->resource->status ? t(AccountStatuses::STATUSES[$this->resource->status]) : '',
            'name' => $this->resource->name,
            'currency' => $this->resource->currency,
            'country' => $this->resource->country,
        ];

        if ($this->withAnyRelation) {
            if ($this->withWireDetails) {
                $dataArray['wireDetails'] = new WireAccountDetailResource($this->resource->wire);
            } else {
                $dataArray['cryptoDetails'] = new CryptoAccountDetailResource($this->resource->cryptoAccountDetail);
            }
        }

        if ($this->walletBlockedStatus) {
            $dataArray['isBlocked'] = $this->resource->cryptoAccountDetail->blocked;
        }

        $dataArray = array_merge($dataArray, [
            'balance' => $this->resource->updateBalance(),
            'createdAt' => $this->resource->created_at->toDateTimeString(),
            'updatedAt' => $this->resource->updated_at->toDateTimeString(),
        ]);

        return $dataArray;
    }


    /**
     * @param bool $withWireDetails
     */
    public function setWithWireDetails(bool $withWireDetails): AccountResource
    {
        $this->withWireDetails = $withWireDetails;

        return $this;
    }

    /**
     * @param bool $walletBlockedStatus
     */
    public function setWalletBlockedStatus(bool $walletBlockedStatus): AccountResource
    {
        $this->walletBlockedStatus = $walletBlockedStatus;

        return $this;
    }

    /**
     * @param bool $withAnyRelation
     */
    public function setWithAnyRelation(bool $withAnyRelation): AccountResource
    {
        $this->withAnyRelation = $withAnyRelation;

        return $this;
    }



    /**
     * Create new anonymous resource collection.
     *
     * @param mixed $resource
     * @param callable|null $each
     *
     * @return AnonymousResourceCollection
     */
    public static function collection($resource, callable $each = null): AnonymousResourceCollection
    {
        $collection = new AnonymousResourceCollection($resource, \get_called_class());

        if ($resource && (! $resource instanceof MissingValue) && $each) {
            $collection->resource->each($each);
        }

        return $collection;
    }
}

<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\AccountStatuses;
use App\Enums\PaymentProvider;
use App\Models\Account;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Account $resource
 */
class ProviderAccountResource extends JsonResource
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
            'id' => $this->resource->id,
            'status' => PaymentProvider::getName($this->resource->status),
            'name' => $this->resource->name ?? '',
            'currency' => $this->resource->currency ?? '',
            'providerLocation' => $this->resource->country ?? '',
            'availableCountryCodes' => $this->resource->countries()->pluck('country'),
            'wireDetails' => new WireAccountDetailResource($this->resource->wire)
        ];
    }
}

<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\CryptoAccountDetail;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property CryptoAccountDetail $resource
 */
class CryptoAccountDetailResource extends JsonResource
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

        $dataArray = [
            'label' => $this->resource->label,
            'address' => $this->resource->address,
        ];

        if ($this->resource->account->is_external) {
            $dataArray['verifiedAt'] = $this->resource->verified_at ? $this->resource->verified_at->toDateTimeString() : '';
            $dataArray['expiry'] = $this->resource->verified_at ? (30 - \Carbon\Carbon::now()->diffInDays($this->resource->verified_at) . ' days') : '';
        }

        return $dataArray;
    }
}

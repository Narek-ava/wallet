<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Country $resource
 */
class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'countryCode' => $this->resource->code,
            'countryName' => $this->resource->name,
            'isBanned' => (bool) $this->resource->is_banned,
            'phoneCode' => $this->resource->phone_code,
        ];
    }
}

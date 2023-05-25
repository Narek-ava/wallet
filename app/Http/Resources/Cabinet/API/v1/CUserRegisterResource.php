<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Cabinet\CUser;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @property CUser $resource
 */
class CUserRegisterResource extends JsonResource
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
            "email" => $this->resource->email,
            "phone" => $this->resource->phone,
            'accessToken' => $this->resource->tokenDetails->access_token ?? null,
            'refreshToken' => $this->resource->tokenDetails->refresh_token ?? null,
        ];
    }
}

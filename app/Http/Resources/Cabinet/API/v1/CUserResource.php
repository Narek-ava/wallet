<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\Cabinet\CUser;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @property CUser $resource
 */
class CUserResource extends JsonResource
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
            'twoFaRequired' => (bool) $this->resource->two_fa_type,
            'twoFAId' => $this->resource->twoFAId ?? null,
            'tokens' => [
                'accessToken' => $this->resource->tokenDetails->access_token ?? null,
                'refreshToken' => $this->resource->tokenDetails->refresh_token ?? null,
            ],
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Models\ReferralLink;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralLinkResource extends JsonResource
{
    /**
     * @var ReferralLink|null
     */
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if(empty($this->resource)) {
            return [];
        }
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'partner_id' => $this->resource->partner_id,
            'individual_rate_templates' => $this->resource->individualRate->name,
            'corporate_rate_templates' => $this->resource->corporateRate->name,
            'activation_date' => $this->resource->activation_date,
            'deactivation_date' => $this->resource->deactivation_date,
            'edit_url' => route('referral-links.edit', $this->resource),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}

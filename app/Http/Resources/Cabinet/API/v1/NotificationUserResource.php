<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\NotificationStatuses;
use App\Models\NotificationUser;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Lang;


/**
 * @property NotificationUser $resource
 */
class NotificationUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if (!$this->resource) {
            return [];
        }

        return [
            'id' => $this->resource->id,
            'status' => NotificationStatuses::getName($this->resource->status),
            "titleMessage" => Lang::has( 'cratos.' . $this->resource->notification->title_message) ? t($this->resource->notification->title_message, json_decode($this->resource->notification->title_params, true)) :  $this->resource->notification->title_message,
            "shortBody" => $this->resource->notification->shortBody,
            'viewedAt' => $this->resource->viewed_at,
            'createdAt' => $this->resource->created_at->toDateTimeString(),
        ];
    }
}

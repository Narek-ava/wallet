<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Models\TicketMessage;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketMessageResource extends JsonResource
{

    /**
     * @var TicketMessage
     */
    public $resource;

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
            'creatorName' => $this->resource->creatorName,
            'viewed' => $this->resource->viewed,
            'file' => $this->resource->file,
            'message' => nl2br($this->resource->message),
            'ticketId' => $this->resource->ticket_id,
            'createdAt' => $this->resource->created_at->toDateTimeString(),
            'updatedAt' => $this->resource->updated_at->toDateTimeString(),
        ];
    }
}

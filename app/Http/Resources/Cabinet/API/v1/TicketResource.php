<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\TicketStatuses;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Cabinet\API\v1\TicketMessageResource;


/**
 * @property Ticket $resource
 */
class TicketResource extends JsonResource
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
            'ticketId' => $this->resource->ticket_id,
            'subject' => $this->resource->subject,
            'question' => $this->resource->question,
            'status' => TicketStatuses::getName($this->resource->status),
            'toClient' => $this->resource->user->cProfile->getFullName(),
            'createdByClient' => $this->resource->createdByClient,
            'messages' => TicketMessageResource::collection($this->resource->messages()->orderByDesc('created_at')->get()),
            'createdAt' => $this->resource->created_at->toDateTimeString(),
            'updatedAt' => $this->resource->updated_at->toDateTimeString(),
            ];
    }
}

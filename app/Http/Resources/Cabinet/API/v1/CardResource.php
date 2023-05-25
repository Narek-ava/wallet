<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\CardTypes;
use App\Enums\WallesterCardStatuses;
use App\Enums\WallesterCardTypes;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $card_number
 * @property string $id
 * @property bool $is_verified
 * @property int $type
 * @property-read Account $account
 */
class CardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last4' => !substr($this->card_mask, -4)?"####":substr($this->card_mask, -4),
            'balance' => $this->account->balance,
            'verified' => $this->is_paid,
            'status' => t(WallesterCardStatuses::NAMES[$this->status]),
            'cardType' => WallesterCardTypes::CARD_TYPES_LOWER[$this->card_type],
            'paymentSystemName' => CardTypes::TYPE_VISA_KEY,
            'creationDate' => date_format($this->created_at,"Y-m-d H:i:s")
        ];
    }
}

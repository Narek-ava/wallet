<?php

namespace App\Http\Resources;

use App\Enums\CardTypes;
use App\Enums\WallesterCardTypes;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $account_id
 * @property string $wallester_account_id
 * @property integer $card_type
 * @property integer $status
 * @property integer $contactless_purchases
 * @property string $name
 * @property integer $atm_withdrawals
 * @property integer $internet_purchases
 * @property integer $overall_limits_enabled
 * @property bool $is_confirmed
 * @property string $card_mask
 * @property mixed $limits
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $wallester_card_id
 * @property mixed $is_blocked
 * @property mixed $is_paid
 * @property mixed $operation_id
 * @property mixed $additional_data
 */
class WallesterAccountDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray( $request)
    {

        return array(
            'id' => $this->id,
            'account_id' => $this->account_id,
            'name' => $this->name,
            'wallester_account_id' => $this->wallester_account_id,
            'card_type' => WallesterCardTypes::CARD_TYPES_LOWER[$this->card_type],
            'status' => $this->status,
            'contactless_purchases' => $this->contactless_purchases,
            'atm_withdrawals' => $this->atm_withdrawals,
            'internet_purchases' => $this->internet_purchases,
            'overall_limits_enabled' => $this->overall_limits_enabled,
            'payment_method' => CardTypes::TYPE_VISA_KEY,
            'is_confirmed' => $this->is_confirmed,
            'card_mask' => $this->card_mask,
            'limits' => json_decode($this->additional_data)->limits,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'wallester_card_id' => $this->wallester_card_id,
            'is_blocked' => $this->is_blocked,
            'is_paid' => $this->is_paid,
            'operation_id' => $this->operation_id,
        );
    }
}

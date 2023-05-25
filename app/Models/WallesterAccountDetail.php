<?php


namespace App\Models;

use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\WallesterCardTypes;
use App\Models\Cabinet\CProfile;

/**
 * Class WallesterAccountDetail
 * @package App\Models
 * @property string $id
 * @property string $account_id
 * @property string $wallester_account_id
 * @property int $status
 * @property int $payment_method
 * @property int $delivery_status
 * @property bool $contactless_purchases
 * @property bool $is_confirmed
 * @property bool $is_blocked
 * @property bool $atm_withdrawals
 * @property bool $internet_purchases
 * @property bool $overall_limits_enabled
 * @property int $card_type
 * @property string $name
 * @property string $operation_id
 * @property string $card_mask
 * @property string $password_3ds
 * @property string $cvv
 * @property string $wallester_card_id
 * @property $expiry_date
 * @property $additional_data
 * @property $created_at
 * @property $updated_at
 *
 * @property Account $account
 * @property BankAccountTemplate $bankAccountTemplate
 * @property CardDeliveryAddress $cardDeliveryAddress
 */
class WallesterAccountDetail extends BaseModel
{
    const CARD_SETTING_KEYS = [
        WallesterCardTypes::TYPE_PLASTIC => 'plastic_card_amount',
        WallesterCardTypes::TYPE_VIRTUAL => 'virtual_card_amount',
    ];

    const SECURITY_YES_OR_NO = [
        'No', 'Yes'
    ];

    protected $fillable = [
        'account_id', 'name', 'wallester_account_id', 'card_type', 'status', 'delivery_status',
        'contactless_purchases', 'atm_withdrawals', 'internet_purchases', 'overall_limits_enabled',
        'password_3ds', 'payment_method', 'is_confirmed', 'card_mask', 'wallester_card_id', 'is_blocked', 'operation_id', 'additional_data'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function cardDeliveryAddress()
    {
        return $this->hasOne(CardDeliveryAddress::class, 'wallester_account_detail_id', 'id');
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }

    public function bankAccountTemplate()
    {
        return $this->hasOne(BankAccountTemplate::class, 'wallester_account_detail_id', 'id');
    }

    public function hasPendingOperationWithTransactions()
    {
        $additionalData = '"wallester_account_detail_id":"' . $this->id . '"';
        $operation = Operation::query()
            ->where('status', OperationStatuses::PENDING)
            ->where('additional_data',  'like', '%' . $additionalData . '%')
            ->first();

        if ($operation && in_array($operation->operation_type, [OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA, OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT])) {
            return true;
        }
        return $operation && !$operation->transactions->isEmpty();
    }
}

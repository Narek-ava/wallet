<?php

namespace App\Models;

use App\Enums\AccountStatuses;
use App\Enums\PaymentFormTypes;
use App\Models\Cabinet\CProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentFormAttempt
 * @property string $id
 * @property string $payment_form_id
 * @property string $profile_id
 * @property string $to_account_id
 * @property string $recipient_account_id
 * @property string $operation_id
 * @property string $phone
 * @property string $email
 * @property string $wallet_address
 * @property float $amount
 * @property string $to_currency
 * @property float $incoming_fee
 * @property float $first_name
 * @property float $last_name
 * @property string $from_currency
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property CProfile $cProfile
 * @property Account $toAccount
 * @property Account $recipientAccount
 * @property Operation $operation
 * @property PaymentForm $paymentForm
 */
class PaymentFormAttempt extends BaseModel
{
    protected $table = 'payment_form_attempts';

    protected $fillable = ['payment_form_id', 'profile_id', 'to_account_id', 'incoming_fee', 'recipient_account_id', 'operation_id', 'phone', 'email', 'amount', 'wallet_address', 'to_currency', 'from_currency', 'first_name', 'last_name', 'created_at', 'updated_at'];

    public function cProfile()
    {
        return $this->belongsTo(CProfile::class, 'profile_id', 'id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id', 'id');
    }

    public function recipientAccount()
    {
        return $this->belongsTo(Account::class, 'recipient_account_id', 'id');
    }

    public function paymentForm()
    {
        return $this->belongsTo(PaymentForm::class, 'payment_form_id', 'id');
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }

    public function setRecipientAccount(CProfile $recipientCProfile, string $cryptocurrency)
    {
        $paymentForm = $this->paymentForm;

        if ($paymentForm->type == PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM) {
            return;
        }

        if ($paymentForm->type == PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM) {
            $this->recipient_account_id = $paymentForm->activeAccounts()->where('accounts.currency', $cryptocurrency)->first()->id ?? null;
        } else {
            $this->recipient_account_id = $recipientCProfile->accounts()->where([
                'accounts.currency' => $cryptocurrency,
                'accounts.status' => AccountStatuses::STATUS_ACTIVE,
                'accounts.is_external' => 0
            ])->first()->id ?? null;
        }

        $this->save();
    }

    public function getPayerFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}

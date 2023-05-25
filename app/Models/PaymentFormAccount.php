<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $form_id
 * @property string $currency
 * @property string $account_id
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class PaymentFormAccount extends Model
{
    protected $table = 'payment_form_account';
}

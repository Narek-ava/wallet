<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentFormAccount;

class FixPaymentFormsAccountStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $paymentFormToAccountUniqueAssignments = PaymentFormAccount::query()
            ->groupBy('form_id', 'account_id')
            ->pluck('id')
            ->toArray();

        PaymentFormAccount::query()
            ->whereNotIn('id', $paymentFormToAccountUniqueAssignments)
            ->delete();

        $paymentFormCurrencies = [];

        $paymentForms = \App\Models\PaymentForm::all();
        foreach ($paymentForms as $paymentForm) {
            $accounts = $paymentForm->activeAccounts()->orderByDesc('account_id')->get();
            $paymentFormCurrencies[$paymentForm->id] = [];
            foreach ($accounts as $account) {
                if (!in_array($account->currency, $paymentFormCurrencies[$paymentForm->id])) {
                    $paymentFormCurrencies[$paymentForm->id][]= $account->currency;
                } else {
                    $account->status = \App\Enums\AccountStatuses::STATUS_DISABLED;
                    $account->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

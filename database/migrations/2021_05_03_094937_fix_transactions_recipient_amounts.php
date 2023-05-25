<?php

use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixTransactionsRecipientAmounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transactions = Transaction::query()->get();
        foreach ($transactions as $transaction) {
            if ($transaction->type != \App\Enums\TransactionType::EXCHANGE_TRX) {
                $transaction->recipient_amount = $transaction->trans_amount;
            } elseif (!$transaction->recipient_amount) {
                $transaction->recipient_amount = round($transaction->trans_amount / $transaction->exchange_rate, 10);
            }
            $transaction->save();
            echo $transaction->recipient_amount,"\n";
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

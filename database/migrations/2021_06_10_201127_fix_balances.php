<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixBalances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $accounts = \App\Models\Account::query()->where('balance', '!=', 0)->get();
        foreach ($accounts as $account) {
            /* @var \App\Models\Account $account*/
            echo "balance before {$account->balance} \n";
            $account->updateBalance();
            echo "after {$account->balance} \n";
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CardAccountsNameFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $accounts = \App\Models\Account::query()
            ->where('owner_type', \App\Enums\AccountType::ACCOUNT_OWNER_TYPE_CLIENT)
            ->whereHas('cardAccountDetail')->get();
        foreach ($accounts as $account) {
            $account->name = $account->cardAccountDetail->card_number;
            $account->save();
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

<?php

use App\Models\ClientSystemWallet;
use Illuminate\Database\Migrations\Migration;

class DeleteNotUsedClientSystemWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        foreach (\App\Enums\Currency::TOKENS_WITH_SUBTOKENS as $subtokens) {
            ClientSystemWallet::query()->whereIn('currency', $subtokens)->delete();
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

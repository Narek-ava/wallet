<?php

use App\Enums\Currency;
use App\Models\ClientSystemWallet;
use Illuminate\Database\Migrations\Migration;

class AddNewCoinsToClientSystemWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $newCoins = array_diff(Currency::getList(), [
            Currency::CURRENCY_BTC,
            Currency::CURRENCY_LTC,
            Currency::CURRENCY_BCH,
        ]);

        foreach ($newCoins as $currency) {
            $clientWallet = new ClientSystemWallet();
            $clientWallet->currency = $currency;
            $clientWallet->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $newCoins = array_diff(Currency::getList(), [
            Currency::CURRENCY_BTC,
            Currency::CURRENCY_LTC,
            Currency::CURRENCY_BCH,
        ]);

        ClientSystemWallet::whereIn('currency', array_values($newCoins))->delete();
    }
}

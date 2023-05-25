<?php

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinsToAccountsTable extends Migration
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
            Account::create([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'currency' => $currency,
                'account_type' => AccountType::TYPE_CRYPTO,
                'name' => "System account {$currency}"
            ]);
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

        Account::query()->whereIn('currency', array_values($newCoins))->delete();
    }
}

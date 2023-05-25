<?php

use App\Enums\{AccountType, Currency};
use App\Models\Account;
use Illuminate\{Database\Migrations\Migration, Support\Str};

class AddSystemGbpAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (AccountType::ACCOUNT_WIRE_TYPES as $wireType => $wireName) {
            Account::create([
                'id' => Str::uuid(),
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'currency' => Currency::CURRENCY_GBP,
                'account_type' => $wireType,
                'name' => "System account {$wireName} " . Currency::CURRENCY_GBP,
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
        Account::query()->where('currency', Currency::CURRENCY_GBP)
            ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
            ->whereNull('payment_provider_id')
            ->delete();
    }
}

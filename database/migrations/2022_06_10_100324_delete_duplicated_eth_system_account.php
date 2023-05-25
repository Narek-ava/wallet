<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;

class DeleteDuplicatedEthSystemAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Account::query()->where([
            'owner_type' => \App\Enums\AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
            'currency' => \App\Enums\Currency::CURRENCY_ETH,
            'status' => \App\Enums\AccountStatuses::STATUS_ACTIVE
        ])->whereNull('payment_provider_id')
            ->orderBy('created_at')->limit(1)->delete();
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

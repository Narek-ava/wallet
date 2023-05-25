<?php

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateFiatSystemAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Currency::FIAT_CURRENCY_NAMES as $currency) {
            $account = Account::query()->where('currency', $currency)
                ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                ->whereNull('payment_provider_id')
                ->where('account_type', AccountType::TYPE_FIAT)
                ->first();
            if (!$account) {
                Account::create([
                    'id' => Str::uuid(),
                    'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                    'currency' => $currency,
                    'account_type' => AccountType::TYPE_FIAT,
                    'name' => "System account fiat {$currency}"
                ]);
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
        Schema::dropIfExists('fiat_system_account');
    }
}

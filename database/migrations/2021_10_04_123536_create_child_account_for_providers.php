<?php

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChildAccountForProviders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $providerAccounts = \App\Models\Account::query()->where([
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
            'status' => AccountStatuses::STATUS_ACTIVE,
            'c_profile_id' => null
        ])->whereNotNull('payment_provider_id')->whereDoesntHave('childAccount')->get();

        foreach ($providerAccounts as $providerAccount) {
            $childAccount = new \App\Models\Account();
            $childAccount->fill([
                'status' => AccountStatuses::STATUS_ACTIVE,
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_PROVIDER,
                'payment_provider_id' => $providerAccount->payment_provider_id,
                'name' => $providerAccount->name . ' Commissions',
                'account_type' => $providerAccount->account_type,
                'currency' => $providerAccount->currency,
                'balance' => 0,
                'parent_id' => $providerAccount->id,
                'country' => $providerAccount->country,
            ]);
            $childAccount->save();
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

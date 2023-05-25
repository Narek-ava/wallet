<?php

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\AccountClientPolicy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientPoliciesForProviders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $accounts = Account::query()
            ->where([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'status' => AccountStatuses::STATUS_ACTIVE
            ])
            ->whereNotNull('payment_provider_id')
            ->whereHas('wire')
            ->get();

        foreach ($accounts as $account) {
            foreach (AccountType::WIRE_PROVIDER_TYPES as $type) {
                $policy  = new AccountClientPolicy();
                $policy->account_id = $account->id;
                $policy->type = $type;
                $policy->save();
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
        //
    }
}

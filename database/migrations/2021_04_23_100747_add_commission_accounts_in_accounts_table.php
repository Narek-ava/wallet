<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddCommissionAccountsInAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $accounts = Account::query()
            ->where('owner_type', \App\Enums\AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
            ->whereNotNull('payment_provider_id')
            ->whereNull('parent_id')->get();
        foreach ($accounts as $account) {
            $commissionAccount = Account::where('parent_id', $account->id)->first();
            if (!$commissionAccount) {
                echo "no commission for {$account->name}";
                $commissionAccount = new Account([
                    'id' => Str::uuid(),
                    'parent_id' => $account->id,
                    'status' => $account->status,
                    'name' => $account->name . ' Commissions',
                    'account_type' => $account->account_type,
                    'owner_type' => \App\Enums\AccountType::ACCOUNT_OWNER_TYPE_PROVIDER,
                    'currency' => $account->currency,
                    'country' => $account->country,
                    'payment_provider_id' => $account->payment_provider_id,
                ]);
                $commissionAccount->save();
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
        Schema::table('accounts', function (Blueprint $table) {
            //
        });
    }
}

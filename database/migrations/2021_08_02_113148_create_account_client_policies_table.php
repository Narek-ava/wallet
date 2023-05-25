<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAccountClientPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_client_policies', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('account_id')->nullable();
            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');
            $table->integer('type');
        });

        $accounts = \App\Models\Account::where('owner_type', \App\Enums\AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
            ->whereNotNull('payment_provider_id')
            ->whereHas('wire')
            ->get();

        foreach ($accounts as $account) {
            foreach (\App\Enums\AccountType::WIRE_PROVIDER_TYPES as $type) {
                $policy  = new \App\Models\AccountClientPolicy();
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
        Schema::dropIfExists('account_client_policies');
    }
}

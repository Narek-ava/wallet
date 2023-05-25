<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteFieldsFromAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->renameColumn('c_profile_id', 'owner');
            $table->renameColumn('account_type', 'owner_type');
            $table->renameColumn('type', 'account_type');
            $table->renameColumn('amount', 'balance');
            $table->dropColumn('account_owner_type');
            $table->dropColumn('IBAN');
            $table->dropColumn('SWIFT');
            $table->dropColumn('card_number');
            $table->dropColumn('crypto_wallet');
            $table->dropColumn('country');
            $table->dropColumn('holder');
            $table->dropColumn('number');
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_address');
        });
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

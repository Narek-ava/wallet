<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->string('type')
                   ->comment('1 - Wire_TopUp, 2 - CC_TopUp, 3 - Crypto_TopUp, 4 - Wire_Withdrawal, 5 - CC_Withdrawal, 6 - Crypto_Withdrawal')
                   ->change();
            $table->decimal('amount')->nullable()->after('type');
            $table->string('from_currency')->nullable()->after('amount');
            $table->string('to_currency')->nullable()->after('from_currency');
            $table->uuid('from_account')->nullable()->after('to_currency');
            $table->uuid('to_account')->nullable()->after('from_account');
            $table->timestamp('confirm_date')->nullable()->after('to_account');
            $table->uuid('confirm_doc')->nullable()->after('confirm_date');
            $table->decimal('exchange_rate', \C\MONEY_LENGTH, \C\MONEY_SCALE)->nullable()->after('confirm_doc'); //
            $table->decimal('client_rate')->nullable()->after('exchange_rate');
            $table->uuid('created_by')->nullable()->after('client_rate');


//            $table->dropColumn('c_profile_id');
//            $table->dropColumn('b_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operations', function (Blueprint $table) {
            //
        });
    }
}

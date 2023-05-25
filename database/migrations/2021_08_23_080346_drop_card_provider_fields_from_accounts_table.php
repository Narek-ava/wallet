<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCardProviderFieldsFromAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('secure');
            $table->dropColumn('payment_system');
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
            $table->integer('region')->after('type_account')->nullable();
            $table->integer('secure')->after('region')->nullable();
            $table->integer('payment_system')->after('secure')->nullable();
        });
    }
}

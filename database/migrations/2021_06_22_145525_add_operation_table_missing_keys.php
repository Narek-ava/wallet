<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationTableMissingKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->foreign('from_account')->references('id')->on('accounts')->onDelete('SET NULL');
            $table->foreign('to_account')->references('id')->on('accounts')->onDelete('SET NULL');
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
            $table->dropForeign('operations_from_account_foreign');
            $table->dropForeign('operations_to_account_foreign');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionsColumnToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('from_commission_id')->nullable()->after('exchange_rate');
            $table->uuid('to_commission_id')->nullable()->after('from_commission_id');
            $table->foreign('from_commission_id')->references('id')->on('commissions')->onDelete('cascade');
            $table->foreign('to_commission_id')->references('id')->on('commissions')->onDelete('cascade');
            $table->dropColumn('recipient_currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
}

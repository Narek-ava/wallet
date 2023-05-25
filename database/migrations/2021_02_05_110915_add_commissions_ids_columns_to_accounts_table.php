<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommissionsIdsColumnsToAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->uuid('from_commission_id')->nullable()->after('payment_provider_id');
            $table->foreign('from_commission_id')->references('id')->on('commissions')->onDelete('cascade');
            $table->uuid('to_commission_id')->nullable()->after('from_commission_id');
            $table->foreign('to_commission_id')->references('id')->on('commissions')->onDelete('cascade');
            $table->uuid('internal_commission_id')->nullable()->after('to_commission_id');
            $table->foreign('internal_commission_id')->references('id')->on('commissions')->onDelete('cascade');
            $table->uuid('refund_commission_id')->nullable()->after('internal_commission_id');
            $table->foreign('refund_commission_id')->references('id')->on('commissions')->onDelete('cascade');
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
            $table->dropForeign('from_commission_id');
            $table->dropForeign('to_commission_id');
            $table->dropForeign('internal_commission_id');
            $table->dropForeign('refund_commission_id');
        });
    }
}

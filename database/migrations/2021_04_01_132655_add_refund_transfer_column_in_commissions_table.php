<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefundTransferColumnInCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn('refund_transfer_usd');
            $table->dropColumn('refund_transfer_eur');
            $table->double('refund_transfer')->nullable()->after('refund_transfer_percent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn('refund_transfer');
            $table->double('refund_transfer_eur')->nullable()->after('refund_transfer_percent');
            $table->double('refund_transfer_usd')->nullable()->after('refund_transfer_eur');
        });
    }
}

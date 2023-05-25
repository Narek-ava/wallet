<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefundFieldsInCommissonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->double('refund_transfer_percent')->nullable()->after('currency');
            $table->double('refund_transfer_eur')->nullable()->after('refund_transfer_percent');
            $table->double('refund_transfer_usd')->nullable()->after('refund_transfer_eur');
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
            $table->dropColumn('refund_transfer_percent');
            $table->dropColumn('refund_transfer_eur');
            $table->dropColumn('refund_transfer_usd');
        });
    }
}

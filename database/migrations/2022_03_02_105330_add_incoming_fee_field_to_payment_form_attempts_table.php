<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncomingFeeFieldToPaymentFormAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_form_attempts', function (Blueprint $table) {
            $table->double('incoming_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_form_attempts', function (Blueprint $table) {
            $table->dropColumn('incoming_fee');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountFieldToPaymentFormAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_form_attempts', function (Blueprint $table) {
            $table->decimal('amount')->nullable()->after('wallet_address');
            $table->string('wallet_address')->nullable()->change();
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
            $table->dropColumn('amount');
            $table->string('wallet_address')->nullable(false)->change();
        });
    }
}

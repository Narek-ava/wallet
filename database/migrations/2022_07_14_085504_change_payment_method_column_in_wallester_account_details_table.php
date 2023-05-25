<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePaymentMethodColumnInWallesterAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallester_account_details', function (Blueprint $table) {
            $table->integer('payment_method')->comment('0-none 1-Card 2-Sepa 3-Crypto')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallester_account_details', function (Blueprint $table) {
            $table->integer('payment_method')->comment('1-Card 2-Sepa 3-Crypto')->nullable(false)->change();

        });
    }
}

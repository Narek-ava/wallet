<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLimitsDecimalColumnsLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->decimal('transaction_amount_max', 13,3)->nullable()->default(null)->change();
            $table->decimal('transaction_amount_min', 13,3)->nullable()->default(null)->change();
            $table->decimal('monthly_amount_max', 13,3)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->decimal('transaction_amount_max', 9,3)->nullable()->default(null)->change();
            $table->decimal('transaction_amount_min', 9,3)->nullable()->default(null)->change();
            $table->decimal('monthly_amount_max', 9,3)->nullable()->default(null)->change();
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLimitsFieldsToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->decimal('transaction_amount_max', 9, 3)->nullable()->change();
            $table->decimal('transaction_amount_min', 9, 3)->nullable()->change();
            $table->decimal('monthly_amount_max', 9, 3)->nullable()->change();
            $table->integer('transaction_count_daily_max')->nullable()->change();
            $table->integer('transaction_count_monthly_max')->nullable()->change();
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
            $table->decimal('transaction_amount_max', 9, 3)->nullable(false)->change();
            $table->decimal('transaction_amount_min', 9, 3)->nullable(false)->change();
            $table->decimal('monthly_amount_max', 9, 3)->nullable(false)->change();
            $table->integer('transaction_count_daily_max')->nullable(false)->change();
            $table->integer('transaction_count_monthly_max')->nullable(false)->change();
        });
    }
}

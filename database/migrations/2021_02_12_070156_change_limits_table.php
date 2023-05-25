<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->dropColumn('sort');
            $table->dropColumn('max_amount');
            $table->dropColumn('transaction_limit');
            $table->dropColumn('min_amount');
            $table->dropColumn('monthly_limit');
            $table->dropColumn('trx_per_day');
            $table->dropColumn('trx_per_month');
            $table->decimal('transaction_amount_max', 9, 3)->after('id');
            $table->decimal('transaction_amount_min', 9, 3)->after('transaction_amount_max');
            $table->decimal('monthly_amount_max', 9, 3)->after('transaction_amount_min');
            $table->integer('transaction_count_daily_max')->after('monthly_amount_max');
            $table->integer('transaction_count_monthly_max')->after('transaction_count_daily_max');
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
            $table->dropColumn('transaction_amount_max');
            $table->dropColumn('transaction_amount_min');
            $table->dropColumn('monthly_amount_max');
            $table->dropColumn('transaction_count_daily_max');
            $table->dropColumn('transaction_count_monthly_max');
            $table->integer('sort')->after('id');
            $table->string('transaction_limits')->after('sort');
            $table->string('monthly_limit')->after('transaction_limits');
            $table->string('trx_per_day')->after('monthly_limit');
            $table->string('trx_per_month')->after('trx_per_day');
            $table->double('min_amount')->nullable()->after('trx_per_month');
            $table->double('max_amount')->nullable()->after('min_amount');
        });
    }
}

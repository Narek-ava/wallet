<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->double('min_amount')->nullable()->after('trx_per_month');
            $table->double('max_amount')->nullable()->after('min_amount');
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
            $table->dropColumn('min_amount');
            $table->dropColumn('max_amount');
        });
    }
}

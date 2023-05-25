<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRateTemplateIdInLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('limits', function (Blueprint $table) {
            $table->uuid('account_id')->nullable()->change();
            $table->uuid('rate_template_id')->nullable()->after('account_id');
            $table->foreign('rate_template_id')->references('id')->on('rate_templates')->onDelete('cascade');
            $table->string('trx_per_day')->nullable()->change();
            $table->string('trx_per_month')->nullable()->change();
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
            $table->dropForeign('rate_template_id');
            $table->dropColumn('rate_template_id');
        });
    }
}

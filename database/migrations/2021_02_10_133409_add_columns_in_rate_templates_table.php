<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInRateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->string('opening')->after('currency');
            $table->string('maintenance')->after('opening');
            $table->string('account_closure')->after('maintenance');
            $table->string('referral_remuneration')->after('account_closure');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rate_templates', function (Blueprint $table) {
            $table->dropColumn('opening');
            $table->dropColumn('maintenance');
            $table->dropColumn('account_closure');
            $table->dropColumn('referral_remuneration');
        });
    }
}

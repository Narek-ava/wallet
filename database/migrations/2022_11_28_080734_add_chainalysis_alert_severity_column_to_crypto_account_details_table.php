<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChainalysisAlertSeverityColumnToCryptoAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->string('chainalysis_alert_severity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crypto_account_details', function (Blueprint $table) {
            $table->dropColumn('chainalysis_alert_severity');
        });
    }
}

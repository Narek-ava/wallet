<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentRenewalRequestIntervalToProjectComplianceProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_compliance_providers', function (Blueprint $table) {
            $table->integer('renewal_interval')->nullable()->after('compliance_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_compliance_providers', function (Blueprint $table) {
            $table->dropColumn('renewal_interval');
        });
    }
}

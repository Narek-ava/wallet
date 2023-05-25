<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComplianceProviderToProjectProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_providers', function (Blueprint $table) {
            $table->uuid('compliance_provider_id')->after('provider_id');
            $table->foreign('compliance_provider_id')->references('id')->on('compliance_providers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_providers', function (Blueprint $table) {
            $table->dropColumn('compliance_provider_id');
        });
    }
}

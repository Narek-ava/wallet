<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContextIdInComplianceRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('logs', function (Blueprint $table) {
            $table->renameColumn('contextId', 'context_id');
        });

        Schema::table('compliance_requests', function (Blueprint $table) {
            $table->uuid('context_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compliance_request', function (Blueprint $table) {
            //
        });
    }
}

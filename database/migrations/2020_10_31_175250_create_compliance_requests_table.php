<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplianceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compliance_requests', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('c_profile_id');
            $table->tinyInteger('compliance_level');
            $table->tinyInteger('status')->default(0);
            $table->text('message');
            $table->string('applicant_id');
            $table->timestamps();
            $table->primary('id');

            $table->foreign('c_profile_id')
                ->references('id')
                ->on('c_profiles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compliance_requests');
    }
}

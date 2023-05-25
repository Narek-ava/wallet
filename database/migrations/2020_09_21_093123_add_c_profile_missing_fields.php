<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCProfileMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_profiles', function($table) {
            $table->uuid('compliance_officer_id')->nullable();
            $table->timestamp('date_of_birth')->nullable()->default(NULL);
            $table->string('city',50)->nullable()->default(NULL);
            $table->string('citizenship',50)->nullable()->default(NULL);
            $table->string('zip_code',20)->nullable()->default(NULL);
            $table->string('address',200)->nullable()->default(NULL);

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

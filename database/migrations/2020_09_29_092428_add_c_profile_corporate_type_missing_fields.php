<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCProfileCorporateTypeMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_profiles', function($table) {
            $table->string('registration_number',50)->nullable()->default(NULL);
            $table->string('legal_address',200)->nullable()->default(NULL);
            $table->string('trading_address',200)->nullable()->default(NULL);
            $table->string('linkedin_link',400)->nullable()->default(NULL);
            $table->string('ceo_full_name',100)->nullable()->default(NULL);
            $table->string('interface_language',2)->nullable()->default('en');
            $table->string('currency_rate',3)->nullable()->default(NULL);
            $table->tinyInteger('verification')->nullable()->default(0)->comment('0-Not verified; 1-Level 1; 2-Level 2; 3-Level 3;');


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

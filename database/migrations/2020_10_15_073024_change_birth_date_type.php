<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBirthDateType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //datetime
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->date('date_of_birth')->change();
            $table->dropColumn('registration_date');
        });

        // @todo company_registration_date
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->date('registration_date')->nullable();
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

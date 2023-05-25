<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CProfileRemoveVerifedAndVerificationFieldsAddVerifedEmailField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->dropColumn('verification');
            $table->dropColumn('verified');

            $table->boolean('email_verified')->default(false);
            $table->boolean('phone_verified')->default(false);

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

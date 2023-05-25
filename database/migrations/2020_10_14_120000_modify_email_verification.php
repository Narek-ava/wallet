<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyEmailVerification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->dropColumn('email_verified');
        });
        Schema::table('c_users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
        });
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->tinyInteger('type')->after('id');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_users', function (Blueprint $table) {
            $table->uuid('id');

            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone',15)->nullable(); //?

            $table->uuid('c_profile_id')
                ->nullable() //?
            ;
            $table->foreign('c_profile_id')
                ->references('id')
                ->on('c_profiles');

            $table->tinyInteger('2fa_type')->default(0);

            $table->primary('id');
        });


        /** rather then
            $table->increments('c_user_no')->default(0);
         */
        DB::statement('ALTER Table c_users add `c_user_no` INTEGER NOT NULL UNIQUE AUTO_INCREMENT');

        /** if needed */
        // DB::statement('ALTER Table c_users AUTO_INCREMENT = 1000;');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_users');
    }
}

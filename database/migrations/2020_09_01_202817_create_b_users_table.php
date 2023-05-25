<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_users', function (Blueprint $table) {
            $table->uuid('id')->default(DB::raw('(uuid())'));

            $table->string('email')->unique();
            $table->string('password');

            $table->tinyInteger('2fa_type')->default(0);

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('b_users');
    }
}

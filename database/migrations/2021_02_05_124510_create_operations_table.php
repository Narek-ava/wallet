<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operations', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->integer('type')->comment('1 - deposit, 2 - wire, 3 - swift');
            $table->uuid('c_profile_id')->nullable();
            $table->uuid('b_user_id')->nullable();
            $table->integer('status')->nullable()->comment('1 - pending, 2 - approved');
            $table->timestamps();

            $table->foreign('c_profile_id')->references('id')->on('c_profiles')->onDelete('cascade');
            $table->foreign('b_user_id')->references('id')->on('b_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operations');
    }
}

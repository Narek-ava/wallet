<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->boolean('is_default')->default(false);
            $table->integer('status');
            $table->integer('type_client');
            $table->uuid('profile_id')->nullable();
            $table->foreign('profile_id')->references('id')->on('c_profiles');
            $table->string('currency', 16);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rate_templates');
    }
}

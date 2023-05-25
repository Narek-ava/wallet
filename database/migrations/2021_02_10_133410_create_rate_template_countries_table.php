<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRateTemplateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rate_template_countries', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('country');
            $table->uuid('rate_template_id');
            $table->foreign('rate_template_id')->references('id')->on('rate_templates')->onDelete('cascade');
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
        Schema::dropIfExists('rate_template_countries');
    }
}

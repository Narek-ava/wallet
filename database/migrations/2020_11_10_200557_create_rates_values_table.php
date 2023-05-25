<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatesValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates_values', function (Blueprint $table) {
            $table->string('key')->index();
            $table->tinyInteger('level')->nullable();
            $table->string('crypto')->nullable();
            $table->decimal('value', \C\RATES_SCALE+8, \C\RATES_SCALE)->nullable();

            $table->uuid('rates_category_id')->nullable();
            $table->foreign('rates_category_id')
                ->references('id')
                ->on('rates_categories');

            $table->primary('id');
            $table->uuid('id');
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
        Schema::dropIfExists('rates_values');
    }
}

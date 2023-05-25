<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantOperationsInformationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_operations_information', function (Blueprint $table) {
            $table->uuid('id');

            $table->uuid('operation_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_cc');
            $table->string('phone_number');

            $table->string('email');

            $table->timestamps();

            $table->foreign('operation_id')->references('id')->on('operations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_operations_information');
    }
}

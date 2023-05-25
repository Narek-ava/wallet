<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardDeliveryAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_delivery_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallester_account_detail_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country_code')->nullable();

            $table->timestamps();

            $table->foreign('wallester_account_detail_id')->references('id')->on('wallester_account_details')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('card_delivery_addresses', function (Blueprint $table) {
           $table->dropForeign('card_delivery_addresses_wallester_account_detail_id_foreign');
        });
        Schema::dropIfExists('card_delivery_addresses');
    }
}

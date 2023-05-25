<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_forms', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->integer('type');
            $table->integer('status');

            $table->uuid('card_provider_id');
            $table->foreign('card_provider_id')->references('id')->on('payment_providers')->onDelete('cascade');

            $table->uuid('liquidity_provider_id');
            $table->foreign('liquidity_provider_id')->references('id')->on('payment_providers')->onDelete('cascade');

            $table->uuid('wallet_provider_id');
            $table->foreign('wallet_provider_id')->references('id')->on('payment_providers')->onDelete('cascade');

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
        Schema::dropIfExists('payment_forms');
    }
}

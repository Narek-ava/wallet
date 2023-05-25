<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentFormAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_form_account', function (Blueprint $table) {
            $table->id();

            $table->uuid('form_id');
            $table->foreign('form_id')->references('id')->on('payment_forms')->cascadeOnDelete();

            $table->string('currency');

            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();

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
        Schema::dropIfExists('payment_form_account');
    }
}

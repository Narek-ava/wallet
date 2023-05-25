<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWallesterAccountDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallester_account_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->nullable();
            $table->string('name')->nullable();
            $table->uuid('wallester_account_id')->nullable();
            $table->integer('card_type')->comment('1-virtual 2-plastic');
            $table->integer('status');
//            $table->integer('delivery_status')->nullable();
            $table->boolean('contactless_purchases')->nullable();
            $table->boolean('atm_withdrawals')->nullable();
            $table->boolean('internet_purchases')->nullable();
            $table->boolean('overall_limits_enabled')->nullable();
            $table->string('password_3ds');
            $table->integer('payment_method')->comment('1-Card 2-Sepa 3-Crypto');
            $table->boolean('is_confirmed');
            $table->string('card_mask')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('cvv')->nullable();

            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallester_account_details');
    }
}

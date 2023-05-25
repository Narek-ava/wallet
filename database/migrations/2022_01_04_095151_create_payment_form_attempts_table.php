<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentFormAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_form_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('payment_form_id')->nullable();
            $table->uuid('profile_id')->nullable();
            $table->uuid('to_account_id')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('wallet_address');
            $table->string('from_currency');
            $table->string('to_currency');

            $table->timestamps();

            $table->foreign('profile_id')->references('id')->on('c_profiles')->nullOnDelete();
            $table->foreign('to_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('payment_form_id')->references('id')->on('payment_forms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_form_attempts', function (Blueprint $table) {
            $table->dropForeign('payment_form_attempts_profile_id_foreign');
            $table->dropForeign('payment_form_attempts_to_account_id_foreign');
            $table->dropForeign('payment_form_attempts_payment_form_id_foreign');
        });

        Schema::dropIfExists('payment_form_attempts');
    }
}

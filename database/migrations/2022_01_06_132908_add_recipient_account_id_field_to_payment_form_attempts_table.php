<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientAccountIdFieldToPaymentFormAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_form_attempts', function (Blueprint $table) {
            $table->uuid('recipient_account_id')->after('to_account_id')->nullable();
            $table->foreign('recipient_account_id')->references('id')->on('accounts')->nullOnDelete();

            $table->uuid('operation_id')->after('recipient_account_id')->nullable();
            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
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
            $table->dropForeign('payment_form_attempts_recipient_account_id_foreign');
            $table->dropColumn('recipient_account_id');

            $table->dropForeign('payment_form_attempts_operation_id_foreign');
            $table->dropColumn('operation_id');
        });
    }
}

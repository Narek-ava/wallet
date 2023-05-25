<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');

            $table->tinyInteger('type'); // enum
            $table->decimal('trans_amount', \C\MONEY_LENGTH, \C\MONEY_SCALE); // pseudo MONEY type
            $table->string('trans_currency', \C\CURRENCY_NAME_LENGTH);
            $table->decimal('recipient_amount', \C\MONEY_LENGTH, \C\MONEY_SCALE); // pseudo MONEY type
            $table->string('recipient_currency', \C\CURRENCY_NAME_LENGTH);

            $table->uuid('from_account'); // for local testing ->nullable();
            $table->foreign('from_account')
                ->references('id')
                ->on('accounts');

            $table->uuid('to_account'); // for local testing ->nullable();
            $table->foreign('to_account')
                ->references('id')
                ->on('accounts');

            $table->timestamp('creation_date')->useCurrent();
            $table->timestamp('transaction_due_date')->nullable();
            $table->timestamp('commit_date')->nullable();
            $table->timestamp('confirm_date')->nullable();

            $table->uuid('confirm_doc')->nullable();
            $table->tinyInteger('status'); // enum

            $table->decimal('exchange_rate', \C\MONEY_LENGTH, \C\MONEY_SCALE)->nullable(); // pseudo MONEY type

            $table->uuid('exchange_request_id');
            $table->foreign('exchange_request_id')
                ->references('id')
                ->on('exchange_requests');

            $table->decimal('commission', \C\MONEY_LENGTH, \C\MONEY_SCALE)->nullable(); // pseudo MONEY type

            // no needed 'cos all dates are above
            // $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}

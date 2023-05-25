<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exchange_requests', function (Blueprint $table) {

            $table->uuid('id');
            $table->primary('id');

            $table->tinyInteger('type'); // enum Wire Exchange, CC Exchange, Crypto Exchange
            $table->decimal('trans_amount', \C\MONEY_LENGTH, \C\MONEY_SCALE); // pseudo MONEY type
            $table->tinyInteger('trans_currency');
            $table->decimal('recipient_amount', \C\MONEY_LENGTH, \C\MONEY_SCALE); // pseudo MONEY type
            $table->tinyInteger('recipient_currency');

            $table->uuid('from_account'); // for local testing ->nullable();
            $table->foreign('from_account')
                ->references('id')
                ->on('accounts');

            $table->uuid('to_account'); // for local testing ->nullable();
            $table->foreign('to_account')
                ->references('id')
                ->on('accounts');

            $table->timestamp('creation_date')->useCurrent();
            $table->timestamp('confirm_date')->nullable();

            $table->uuid('confirm_doc')->nullable(); // @todo либо в транзакции, либо тут?
            $table->tinyInteger('status'); // enum Состояние заявки

            $table->decimal('exchange_rate', \C\MONEY_LENGTH, \C\MONEY_SCALE)->nullable(); // pseudo MONEY type
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
        Schema::dropIfExists('exchange_requests');
    }
}

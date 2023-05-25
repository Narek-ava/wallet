<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {

            $table->uuid('id');
            $table->primary('id');

            $table->uuid('c_profile_id'); // for local testing ->nullable();
            $table->foreign('c_profile_id')
                ->references('id')
                ->on('c_profiles');

            $table->string('name')->nullable();
            $table->tinyInteger('type')->nullable()
                ->comment('SWIFT, SEPA, ExternalAcc, InvoiceAcc, TransitAcc, CheckingAcc' );
            $table->string('currency', \C\CURRENCY_NAME_LENGTH);
            $table->decimal('amount', \C\MONEY_LENGTH, \C\MONEY_SCALE)->default(0); // pseudo MONEY type

            /** Данные о "реальных" (external) счетах, "отражением" которого является данный счёт */
            $table->string('IBAN')->nullable()
                ->comment('Номер счёта в банке, с/на который будет реальная транзакция, если этот счёт для Wire');
            $table->string('SWIFT')->nullable()
                ->comment('SWIFT-код банка, с/на который будет реальная транзакция, если этот счёт для Wire');
            $table->string('card_number')->nullable()
                ->comment('Номер карты, с/на которую будет реальная транзакция, если этот счёт для карты');
            $table->string('crypto_wallet')
                ->comment('Номер крипто-кошелька, с/на который будет реальная транзакция, если этот счёт для крипты');
            $table->string('country')->nullable();
            $table->string('holder')->nullable();
            $table->string('number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_address')->nullable();
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
        Schema::dropIfExists('accounts');
    }
}

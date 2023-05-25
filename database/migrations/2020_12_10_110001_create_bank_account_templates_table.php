<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankAccountTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('bank_account_templates', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');

            $table->string('name')->nullable(); // of template
            $table->tinyInteger('type')->nullable()->comment('SWIFT, SEPA');
            $table->uuid('c_profile_id'); // for local testing ->nullable();
            $table->foreign('c_profile_id')
                ->references('id')
                ->on('c_profiles');

            $table->tinyInteger('currency');
            $table->string('country')->nullable();
            $table->string('holder');
            $table->string('number');
            $table->string('bank_name');
            $table->string('bank_address');

            $table->string('IBAN');
            $table->string('SWIFT'); // BIC, видимо

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
        Schema::dropIfExists('bank_account_templates');
    }
}

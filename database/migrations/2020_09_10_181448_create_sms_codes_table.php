<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_codes', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')
                ->comment('1 - confirm phone, 2 - 2FA, 3 - one time access code');
            $table->string('phone')->index();
            $table->char('value',4); // @todo \C\SMS_SIZE
// @todo            $table->timestamp('sent_at');
            $table->tinyInteger('attempts')->default(0);
// @todo            $table->timestamp('expires_at');
            $table->timestamp('blocked_till')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_codes');
    }
}

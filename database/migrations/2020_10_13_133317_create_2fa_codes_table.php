<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Create2faCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('2fa_codes', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')
                ->comment('Enums\\TwoFAType');
            $table->char('value', env('CRATOS_2FA_CODE_SIZE', 6));
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->uuid('c_user_id')->unique();
            $table->foreign('c_user_id')
                ->references('id')
                ->on('c_users')
            ;
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
        Schema::dropIfExists('2fa_codes');
    }
}

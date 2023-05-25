<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablesWithCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sms_codes', function (Blueprint $table) {
            $table->string('value', \C\SMS_SIZE)
                ->change();
        });
        Schema::table('2fa_codes', function (Blueprint $table) {
            $table->string('value', \C\TWO_FA_CODE_SIZE)
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

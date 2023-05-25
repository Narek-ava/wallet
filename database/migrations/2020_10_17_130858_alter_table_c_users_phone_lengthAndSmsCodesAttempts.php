<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableCUsersPhoneLengthAndSmsCodesAttempts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_users', function (Blueprint $table) {
            $table->string('phone',\C\PHONE_CC_MAX+\C\PHONE_NO_MAX)
                ->nullable(false)
                ->change();
        });
        Schema::table('sms_codes', function (Blueprint $table) {
            $table->renameColumn('attempts', 'sent_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}

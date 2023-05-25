<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGoogle2faColumnToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_users', function (Blueprint $table) {
            $table->dropColumn('2fa_type');
            // @see \App\Enums\TwoFAType
            $table->tinyInteger('two_fa_type')->default(0)
                ->comment('0-none,1-Google; 2-email;3-SMS');
            $table->text('google2fa_secret');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('c_users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
            $table->dropColumn('two_fa_type');
        });
    }
}

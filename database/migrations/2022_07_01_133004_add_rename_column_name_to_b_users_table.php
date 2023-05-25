<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenameColumnNameToBUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_users', function (Blueprint $table) {
            // @see \App\Enums\TwoFAType
            $table->tinyInteger('two_fa_type')->default(0)->after('status')
                ->comment('0-none,1-Google; 2-email;3-SMS');
            $table->text('google2fa_secret')->after('two_fa_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('b_users', function (Blueprint $table) {
            $table->dropColumn('google2fa_secret');
            $table->dropColumn('two_fa_type');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusTokenTo2faCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('2fa_codes', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->smallInteger('status')->after('attempts')->default(0);
            $table->string('token')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('2fa_codes', function (Blueprint $table) {
            $table->integer('id')->change();
            $table->dropColumn('status');
            $table->dropColumn('token');
        });
    }
}

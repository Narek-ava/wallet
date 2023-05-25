<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWebhookUrlColumnToCProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->string('webhook_url')->nullable();
            $table->string('secret_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('c_profiles', function (Blueprint $table) {
            $table->dropColumn('webhook_url');
            $table->dropColumn('secret_key');
        });
    }
}

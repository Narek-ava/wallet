<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiresTimeRefreshAccessTokensToApiClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_clients', function (Blueprint $table) {
            $table->integer('access_token_expires_time')->after('status')->default(1);
            $table->integer('refresh_token_expires_time')->after('access_token_expires_time')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_clients', function (Blueprint $table) {
            $table->dropColumn('access_token_expires_time');
            $table->dropColumn('refresh_token_expires_time');
        });
    }
}

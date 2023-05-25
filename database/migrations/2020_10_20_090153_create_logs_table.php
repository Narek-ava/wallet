<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('c_user_id')->nullable();
            $table->uuid('b_user_id')->nullable();
            $table->uuid('contextId')->nullable();
            $table->ipAddress('ip')->nullable()->default(NULL);
            $table->tinyInteger('type')->nullable()->default(NULL);
            $table->tinyInteger('result')->comment('1 - success, 2 - neutral, 3- failure')->nullable()->default(NULL);
            $table->tinyInteger('level')
                ->comment('1 - emergency, 2 - alert, 3 - critical, 4 - error, 5 - warning, 6 - notice, 7 - info, 8 - debug')
                ->nullable()->default(NULL);
            $table->text('action')->nullable()->default(NULL);
            $table->json('data')->nullable()->default(NULL);
            $table->string('user_agent', 200)->nullable();
            $table->timestamp('created_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));

            $table->primary('id');
            $table->foreign('c_user_id')
                ->references('id')
                ->on('c_users');
            $table->foreign('b_user_id')
                ->references('id')
                ->on('b_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}

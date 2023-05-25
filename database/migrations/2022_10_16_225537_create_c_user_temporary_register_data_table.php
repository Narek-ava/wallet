<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCUserTemporaryRegisterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_user_temporary_register_data', function (Blueprint $table) {
            $table->id();
            $table->integer('account_type');
            $table->string('email');
            $table->string('phone');
            $table->string('password_encrypted');
            $table->integer('notifications_count')->default(0);
            $table->timestamp('last_notified_at')->nullable();
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
        Schema::dropIfExists('c_user_temporary_register_data');
    }
}

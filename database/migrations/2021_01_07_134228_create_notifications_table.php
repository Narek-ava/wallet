<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('recepient')->default(\App\Enums\NotificationRecipients::CURRENT_CLIENT);
            $table->string('title_message')->nullable();
            $table->string('body_message');
            $table->text('title_params')->nullable();
            $table->text('body_params')->nullable();
            $table->char('b_user_id', 36)->nullable();
            $table->foreign('b_user_id')->references('id')->on('b_users')->onDelete('cascade');
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
        Schema::dropIfExists('notifications');
    }
}

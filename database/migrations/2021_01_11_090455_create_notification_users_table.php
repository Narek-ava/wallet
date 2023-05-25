<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_users', function (Blueprint $table) {
            $table->id();
//            $table->char('c_user_id',36);
//            $table->foreign('c_user_id')->references('id')->on('c_users')->onDelete('cascade');
            $table->char('userable_id', 36);
            $table->string('userable_type');
            $table->tinyInteger('status')->default(\App\Enums\NotificationStatuses::NOT_VIEWED);
            $table->timestamp('viewed_at')->nullable();
            $table->unsignedBigInteger('notification_id');
            $table->foreign('notification_id')->references('id')->on('notifications')->onDelete('cascade');
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
        Schema::dropIfExists('notification_users');
    }
}

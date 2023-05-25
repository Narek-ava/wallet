<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->uuid('id');
            $table->boolean('viewed')->default(false);
            $table->text('message');
            $table->char('ticket_id',36);
            $table->foreign('ticket_id')->references('id')->on('tickets');
            $table->string('file')->nullable();
            $table->uuid('massageable_id');
            $table->string('massageable_type');
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
        Schema::dropIfExists('ticket_messages');
    }
}

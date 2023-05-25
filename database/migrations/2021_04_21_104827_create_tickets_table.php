<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->string('subject')->nullable();
            $table->text('question');
            $table->string('file')->nullable();
            $table->integer('status')->default(\App\Enums\TicketStatuses::STATUS_OPEN);
            $table->integer('ticket_id')->autoIncrement()->unique();
            $table->uuid('to_client');
            $table->foreign('to_client')->references('id')->on('c_users');
            $table->uuid('ticketable_id');
            $table->string('ticketable_type');
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
        Schema::dropIfExists('tickets');
    }
}

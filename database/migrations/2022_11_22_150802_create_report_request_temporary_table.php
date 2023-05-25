<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportRequestTemporaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_request_temporary', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->json('parameters');
            $table->smallInteger('status')->comment('1: new; 2: pending; 3: complete');
            $table->smallInteger('report_type');
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
        Schema::dropIfExists('report_request_temporary');
    }
}

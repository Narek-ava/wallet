<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->double('incoming_funds');
            $table->double('incoming_transfer');
            $table->double('incoming_min_fee');
            $table->double('incoming_min_amount');
            $table->double('incoming_max_amount');
            $table->double('outgoing_founds');
            $table->double('outgoing_transfer');
            $table->double('outgoing_min_fee');
            $table->double('outgoing_min_amount');
            $table->double('outgoing_max_amount');
            $table->double('internal_transfer_percent');
            $table->double('internal_transfer');
            $table->double('internal_min_fee');
            $table->double('internal_min_amount');
            $table->double('internal_max_amount');
            $table->double('refund_transfer_percent');
            $table->double('refund_transfer');
            $table->double('refund_min_fee');
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
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
        Schema::dropIfExists('commissions');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_webhooks', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('crypto_account_detail_id')->nullable();
            $table->text('payload');
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('failed_count')->default(0);
            $table->timestamps();
    
            $table->foreign('crypto_account_detail_id')
                ->references('id')
                ->on('crypto_account_details')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crypto_webhooks');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantWebhookAttemptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchant_webhook_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('webhook_url')->nullable(false);
            $table->uuid('operation_id')->nullable(false);
            $table->uuid('merchant_id')->nullable(false);
            $table->smallInteger('attempts')->default(0);
            $table->integer('status')->default(\App\Models\MerchantWebhookAttempt::STATUS_PENDING);
            $table->mediumText('error_message')->nullable();
            $table->timestamps();

            $table->foreign('operation_id')->references('id')->on('operations')->cascadeOnDelete();
            $table->foreign('merchant_id')->references('id')->on('c_profiles')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchant_webhook_attempts');
    }
}

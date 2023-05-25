<?php

use App\Enums\OperationStatuses;
use App\Services\OperationFeeService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_fees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('operation_id')->nullable(false);
            $table->decimal('client_crypto', 20, 8)->nullable();
            $table->decimal('client_fiat', 20, 8)->nullable();
            $table->decimal('provider_crypto', 20, 8)->nullable();
            $table->decimal('provider_fiat', 20, 8)->nullable();
            $table->decimal('system_crypto', 20, 8)->nullable();
            $table->decimal('system_fiat', 20, 8)->nullable();
            $table->timestamps();

            $table->foreign('operation_id')
                ->references('id')
                ->on('operations')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });


        $query = \App\Models\Operation::query()->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::RETURNED]);
        $query->chunk(100, function ($operations) {
            foreach ($operations as $operation) {
                $operationFeeService = new OperationFeeService();
                $operationFeeService->setOperation($operation);
                $operationFeeService->calculate();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_fees');
    }
}

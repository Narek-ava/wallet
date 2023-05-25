<?php

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Facades\ActivityLogFacade;
use App\Models\Log;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCUserIdToLogsByOperations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logs_by_operations', function (Blueprint $table) {
            $operations = \App\Models\Operation::query()->whereNotNull('c_profile_id')->with('cProfile')->get();

            /* @var \App\Models\Operation $operation*/
            foreach ($operations as $operation) {
                Log::query()->where(['context_id' => $operation->id])->update(['c_user_id' => $operation->cProfile->cUser->id]);

                ActivityLogFacade::saveLog(
                    LogMessage::NEW_OPERATION_CREATED,
                    [
                        'operationNumber' => $operation->operation_id,
                        'operationType' => OperationOperationType::getName($operation->operation_type),
                        'operationAmount' => $operation->amount,
                        'fromCurrency' => $operation->from_currency,
                        'toCurrency' => $operation->to_currency,
                    ],
                    LogResult::RESULT_SUCCESS,
                    LogType::TYPE_NEW_OPERATION_CREATED,
                    $operation->id ,
                    $operation->cProfile->cUser->id,
                    $operation->created_at
                );
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
        Schema::table('logs_by_operations', function (Blueprint $table) {
            //
        });
    }
}

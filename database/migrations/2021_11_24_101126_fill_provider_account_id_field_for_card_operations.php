<?php

use App\Enums\OperationOperationType;
use App\Models\Operation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FillProviderAccountIdFieldForCardOperations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Operation::query()->where('operation_type', OperationOperationType::TYPE_CARD)->chunk(100, function ($operations) {
            foreach ($operations as $operation) {
                $operation->provider_account_id = $operation->getCardProviderIdAccountFromCardTransaction();
                $operation->save();
            }
        });;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

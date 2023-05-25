<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOperationSubstatusAndStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $operations = \App\Models\Operation::query()->where([
            'status' => \App\Enums\OperationStatuses::RETURNED,
            'substatus' => 3
        ])->update([
            'status' => \App\Enums\OperationStatuses::DECLINED,
            'substatus' => null
        ]);
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

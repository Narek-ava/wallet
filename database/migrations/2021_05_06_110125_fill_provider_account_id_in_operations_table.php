<?php

use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Models\PaymentProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FillProviderAccountIdInOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $operations = \App\Models\Operation::whereNotNull('payment_provider_id')
            ->whereNull('provider_account_id')
            ->get();

        foreach($operations as $operation){
            $provider = PaymentProvider::find($operation->payment_provider_id);
            if($operation->operation_type == OperationOperationType::TYPE_TOP_UP_SEPA){
                $type = AccountType::TYPE_WIRE_SEPA;
            }elseif ($operation->operation_type == OperationOperationType::TYPE_TOP_UP_SWIFT){
                $type = AccountType::TYPE_WIRE_SWIFT;
            }elseif ($operation->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO ||
                $operation->operation_type == OperationOperationType::TYPE_WITHDRAW_CRYPTO ){
                $type = AccountType::TYPE_CRYPTO;
            }else{
                $type = null;
            }

            if($type && $provider){
                $providerAccount = $provider->accountByCurrency($operation->from_currency, $type);
                if($providerAccount) {
                    $operation->update([
                        'provider_account_id' => $providerAccount->id
                    ]);
                }
            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operations', function (Blueprint $table) {
            //
        });
    }
}

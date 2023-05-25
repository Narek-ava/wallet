<?php

use App\Services\Wallester\Api;
use App\Services\Wallester\WallesterPaymentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FillBankDetailsForWallesterCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \App\Models\WallesterAccountDetail::query()->whereNull('wallester_account_id')->delete();

        Schema::table('bank_account_templates', function (Blueprint $table) {
            $table->uuid('wallester_account_detail_id')->nullable();

            $table->foreign('wallester_account_detail_id')->references('id')
                ->on('wallester_account_details')->nullOnDelete();
        });

        \App\Models\WallesterAccountDetail::query()
            ->chunk(100, function($wallesterAccountDetails) {
                /* @var Api $wallesterApi */
                $wallesterApi = resolve(Api::class);

                /* @var WallesterPaymentService $wallesterPaymentService */
                $wallesterPaymentService = resolve(WallesterPaymentService::class);

                foreach ($wallesterAccountDetails as $wallesterAccountDetail) {

                    $accountInWallester = $wallesterApi->getAccount($wallesterAccountDetail->wallester_account_id);

                    if (!isset($accountInWallester['account']['viban'])) {
                        $wallesterApi->vIban($wallesterAccountDetail->wallester_account_id);
                    }

                    $accountInWallester = $wallesterApi->getAccount($accountInWallester['account']['id']);

                    if (!$wallesterAccountDetail->bankAccountTemplate) {
                        $wallesterPaymentService->createBankDetails($accountInWallester['account']['top_up_details'] ?? [], $wallesterAccountDetail->card_mask, $accountInWallester['account']['viban'] ?? null, $wallesterAccountDetail->account->cProfile, $wallesterAccountDetail->id);
                    }

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
        Schema::table('bank_account_templates', function (Blueprint $table) {
            $table->dropForeign('bank_account_templates_wallester_account_detail_id_foreign');
            $table->dropColumn('wallester_account_detail_id');
        });

    }
}

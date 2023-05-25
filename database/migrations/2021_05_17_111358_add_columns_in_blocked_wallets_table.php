<?php

use App\Enums\BlockedWalletsStatuses;
use App\Enums\BlockedWalletTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInBlockedWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blocked_wallets', function (Blueprint $table) {
            $table->integer('type')->default(BlockedWalletTypes::TYPE_BLOCKED)->after('wallet_id');
            $table->integer('status')->default(BlockedWalletsStatuses::STATUS_ACTIVE)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blocked_wallets', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('status');
        });
    }
}

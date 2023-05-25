<?php

use App\Enums\AccountType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->uuid('c_profile_id')->nullable()->default(null)->change();
            $table->string('account_owner_type')->default(AccountType::ACCOUNT_OWNER_TYPE_CLIENT)->after('c_profile_id');
            $table->string('account_type')->nullable()->after('account_owner_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['account_owner_type', 'account_type']);
        });
    }
}

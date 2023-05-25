<?php

use App\Models\Cabinet\CUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyToCUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CUser::query()
            ->whereNotNull('payment_form_id')
            ->whereDoesntHave('paymentForm')
            ->update([
                'payment_form_id' => null
            ]);


        Schema::table('c_users', function (Blueprint $table) {
            $table->uuid('payment_form_id')->nullable()->change();
            $table->foreign('payment_form_id')->references('id')->on('payment_forms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('c_users', function (Blueprint $table) {
            $table->dropForeign('c_users_payment_form_id_foreign');
        });
    }
}

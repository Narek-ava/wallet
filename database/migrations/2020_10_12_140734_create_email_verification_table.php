<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailVerificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // @todo status + verified_at + verified_at - expired

        Schema::create('email_verifications', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('c_user_id')->nullable();
            $table->string('new_email',200)->nullable()->default(NULL);
            $table->string('token',16)->nullable()->default(NULL);
            $table->tinyInteger('status')->default(0) ->comment('0 - not verified, 1 - verified');
            $table->timestamp('created_at')->nullable()->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('verified_at')->nullable();
            $table->primary('id');

            $table->foreign('c_user_id')
                ->references('id')
                ->on('c_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_verification');
    }
}

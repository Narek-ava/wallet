<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_owners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('c_profile_id');
            $table->foreign('c_profile_id')
                ->references('id')
                ->on('c_profiles')->cascadeOnDelete();
            $table->string('name',100)->nullable()->default(NULL);
            $table->integer('type')->comment('1 - Beneficial Owner, 2 - CEO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_owners');
    }
}

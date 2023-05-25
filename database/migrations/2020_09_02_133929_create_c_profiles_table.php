<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_profiles', function (Blueprint $table) {

            $table->uuid('id');
            $table->tinyInteger('account_type')
                ->comment('1 - individual account; 2 - corporate account');
            $table->string('first_name',50)->nullable()->default(NULL);
            $table->string('last_name',50)->nullable()->default(NULL);
            $table->string('country')->nullable()->default(NULL); // @todo FK
            $table->string('company_name',150)->nullable()->default(NULL);
            $table->string('company_email',200)->nullable()->default(NULL);
            $table->string('company_phone',15)->nullable()->default(NULL);
            $table->string('industry_type',100)->nullable()->default(NULL);
            $table->string('legal_form',100)->nullable()->default(NULL);
            $table->string('beneficial_owner',100)->nullable()->default(NULL);
            $table->string('contact_email',200)->nullable()->default(NULL);
            $table->tinyInteger('compliance_level')->default(0);
            $table->tinyInteger('status')->default(0) //?
                ->comment('0-New,1-Active; 2-Banned; 3-Closed');

            //? use compliance_level as verified? verified_at? verification log?
            $table->boolean('verified')->default(false);
            $table->timestamp('last_login')->nullable();
            $table->uuid('manager_id')->nullable();
            //? хранить не UUID, а код рефера
            $table->uuid('refferal_of_user')->nullable();

            $table->timestamp('registration_date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();

            $table->primary('id');

        });

        DB::statement('ALTER Table c_profiles add `profile_id` INTEGER NOT NULL UNIQUE AUTO_INCREMENT');
        // DB::statement('ALTER Table c_profiles AUTO_INCREMENT = 1000;');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_profiles');
    }
}

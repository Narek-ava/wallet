<?php

use App\Models\Backoffice\BUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyBUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('b_users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('phone')->after('email')->nullable();
            $table->boolean('is_super_admin')->after('password')->default(\App\Enums\AdminRoles::IS_NOT_SUPER_ADMIN);
            $table->string('password')->nullable()->change();
            $table->boolean('status')->default(\App\Enums\AdminRoles::STATUS_ACTIVE);
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
        Schema::table('b_users', function (Blueprint $table) {
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('is_super_admin');
            $table->string('password')->nullable(false)->change();
            $table->dropColumn('status');
            $table->dropTimestamps();
        });
    }
}

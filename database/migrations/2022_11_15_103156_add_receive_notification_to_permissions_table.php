<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\BUserPermissions;
use Spatie\Permission\Models\Permission;

class AddReceiveNotificationToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Permission::query()->where('name', BUserPermissions::RECEIVE_NOTIFICATIONS)->exists()) {
            Permission::create(['name' => BUserPermissions::RECEIVE_NOTIFICATIONS, 'guard_name' => 'bUser']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Permission::query()->where('name', BUserPermissions::RECEIVE_NOTIFICATIONS)->delete();
    }
}

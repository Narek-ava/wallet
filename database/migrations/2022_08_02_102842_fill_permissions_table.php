<?php

use App\Enums\BUserPermissions;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

class FillPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        foreach (BUserPermissions::NAMES as $name) {
            try {
                Permission::findByName($name, 'bUser');
            } catch (\Throwable $exception) {
                Permission::create(['name' => $name, 'guard_name' => 'bUser']);
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
        Permission::query()->whereIn('name', BUserPermissions::NAMES)->delete();
    }
}

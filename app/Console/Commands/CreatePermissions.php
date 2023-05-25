<?php

namespace App\Console\Commands;

use App\Enums\BUserPermissions;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create permissions for manager roles';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (BUserPermissions::NAMES as $name) {
            try {
                $permission = Permission::findByName($name, 'bUser');
            } catch (\Throwable $exception) {
                Permission::create(['name' => $name, 'guard_name' => 'bUser']);
            }
        }

        return 0;
    }
}

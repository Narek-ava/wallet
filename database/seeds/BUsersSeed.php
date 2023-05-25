<?php

use App\Models\Backoffice\BUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BUsersSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BUser::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        BUser::create([
            'email' => config('cratos.testing.admin_login'),
            'password' => bcrypt(config('cratos.testing.default_password')),
        ]);
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            BUser::create([
                'email' => $faker->email,
                'password' => bcrypt(config('cratos.testing.default_password')),
            ]);
        }

    }
}

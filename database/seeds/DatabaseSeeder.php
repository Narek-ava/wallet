<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(BUsersSeed::class);
         $this->call(CUsersSeed::class);
         $this->call(RatesSeed::class);
         $this->call(SystemAccountSeed::class);
         $this->call(ClientDefaultRateTemplateSeeder::class);
    }
}

<?php

use Illuminate\Database\Seeder;
use App\Models\{Cabinet\CProfile, Cabinet\CUser};
use Illuminate\Support\{Facades\DB, Str};

class CUsersSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CProfile::truncate();
        CUser::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faker = Faker\Factory::create();
        $statusesList = array_keys(\App\Enums\CProfileStatuses::NAMES);
        $complianceLevelList = array_keys(\App\Enums\ComplianceLevel::NAMES);
        $countriesList = array_keys(\App\Models\Country::getCountries(false));
        $languagesList = array_keys(\App\Enums\Language::NAMES);
        $bManagers = \App\Models\Backoffice\BUser::limit(300)->pluck('id');
        try {
            foreach ([1, 2] as $type) {
                $i = 0;
                while ($i < (config('cratos.testing.cuser_count') / 2)) {
                    $cid = Str::uuid();
                    $email = $faker->email;
                    $fields = [
                        'id' => $cid,
                        'account_type' => $type,
                        'first_name' => $faker->firstName,
                        'last_name' => $faker->lastName,
                        'country' => $countriesList[rand(0, count($countriesList) - 1)],
                        'status' => $statusesList[rand(0, count($statusesList) - 1)],
                        'manager_id' => $bManagers[rand(0, count($bManagers) - 1)],
// no!                        'last_login' => $faker->dateTimeBetween(),
                        'compliance_level' => $complianceLevelList[rand(0, count($complianceLevelList) - 1)],
                    ];
                    if ($type == CProfile::TYPE_CORPORATE) {
                        $fields['company_name'] = $faker->company;
                        $fields['company_email'] = $faker->email;
                        $fields['interface_language'] = $languagesList[rand(0, count($languagesList) - 1)];
                        $fields['registration_date'] = $faker->dateTimeBetween();
                    } else {
                        $fields['date_of_birth'] = $faker->dateTimeBetween('-80 years', '-19 years');
                    }
                    $cProfile = CProfile::create($fields);
                    CUser::create([
                        'id' => Str::uuid(),
                        'email' => $email, // @todo no needed?
                        'phone' => substr($faker->phoneNumber, 0, 15),
                        'password' => bcrypt(config('cratos.testing.default_password')),
                        'c_profile_id' => $cid
                    ]);

                    $i++;
                }
            }
        } catch (Exception $e) {

        }
    }
}

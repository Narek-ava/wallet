<?php

use App\Models\Cabinet\CProfile;
use App\Models\CompanyOwners;
use Illuminate\Database\Migrations\Migration;

class FillCompanyOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CProfile::query()->where([
            'account_type' => CProfile::TYPE_CORPORATE,
        ])->chunk(100, function ($profiles) {
            foreach ($profiles as $profile) {
               if ($profile->ceo_full_name) {
                   $companyCEOwner = new CompanyOwners();
                   $companyCEOwner->fill([
                      'c_profile_id' =>  $profile->id,
                      'name' =>  $profile->ceo_full_name,
                      'type' =>  \App\Enums\CompanyOwners::TYPE_CEO,
                   ]);
                   $companyCEOwner->save();
               }
               if ($profile->beneficial_owner) {
                   $companyBeneficialOwner = new CompanyOwners();
                   $companyBeneficialOwner->fill([
                       'c_profile_id' =>  $profile->id,
                       'name' =>  $profile->beneficial_owner,
                       'type' =>  \App\Enums\CompanyOwners::TYPE_BENEFICIAL_OWNER,
                   ]);
                   $companyBeneficialOwner->save();
               }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

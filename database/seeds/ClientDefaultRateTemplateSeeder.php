<?php

use App\Services\CommissionsService;
use App\Services\CProfileService;
use App\Services\CUserService;
use App\Services\LimitsService;
use App\Services\RateTemplateCountriesService;
use App\Services\RateTemplatesService;
use Illuminate\Database\Seeder;

class ClientDefaultRateTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(RateTemplatesService $rateTemplatesService,
                        RateTemplateCountriesService $rateTemplateCountriesService,
                        CommissionsService $commissionsService,
                        LimitsService $limitsService,
                        CProfileService $profilesService)
    {
        $rateTemplateIdIndividual = $rateTemplatesService->store([
            'name' => 'Default Client rate template individual',
            'status' => 1,
            'is_default' => 1,
            'type_client' => 1,
            'opening' => 5,
            'maintenance' => 6,
            'account_closure' => 7,
            'referral_remuneration' => 8
        ], array_keys(\App\Models\Country::getCountries(false)));
        $rateTemplateIdCorporate = $rateTemplatesService->store([
            'name' => 'Default Client rate template corporate',
            'status' => 1,
            'is_default' => 1,
            'type_client' => 2,
            'opening' => 15,
            'maintenance' => 16,
            'account_closure' => 17,
            'referral_remuneration' => 18
        ], array_keys(\App\Models\Country::getCountries(false)));

        $rateTemplateCountriesService->createCountries($rateTemplateIdIndividual, array_keys(\App\Models\Country::getCountries(false)));
        $rateTemplateCountriesService->createCountries($rateTemplateIdCorporate, array_keys(\App\Models\Country::getCountries(false)));

        $commissionsService->createRateTemplateCommission($rateTemplateIdIndividual, 'name',  [
            'percent_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'fixed_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'min_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'max_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'min_amount' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'refund_transfer_percent' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'refund_transfer_usd' => [1,1,1,1,1,1,1,1],
            'refund_transfer_eur' => [1,1,1,1,1,1,1,1]
        ]);
        $commissionsService->createRateTemplateCommission($rateTemplateIdCorporate, 'name',  [
            'percent_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'fixed_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'min_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'max_commission' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'min_amount' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'refund_transfer_percent' => [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            'refund_transfer_usd' => [1,1,1,1,1,1,1,1],
            'refund_transfer_eur' => [1,1,1,1,1,1,1,1]
        ]);

        $limitsService->createClientRateLimits($rateTemplateIdIndividual, [
            'transaction_amount_max' => [1,1,1],
            'monthly_amount_max' => [1,1,1]
        ]);
        $limitsService->createClientRateLimits($rateTemplateIdCorporate, [
            'transaction_amount_max' => [1,1,1],
            'monthly_amount_max' => [1,1,1]
        ]);

        $profilesService->changeDefaultRateTemplates($rateTemplateIdIndividual, $rateTemplateIdCorporate);

    }
}

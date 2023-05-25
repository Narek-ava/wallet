<?php

namespace App\Services;

use App\Models\Country;
use App\Services\Wallester\Api;

class CountryService
{
    public function getFilteredCountries(array $data)
    {
        $query = Country::query()->filterCountries($data['code'] ?? null, $data['banned'] ?? null, $data['name'] ?? null, $data['phone_code'] ?? null);

        return $query->paginate(10);
    }


    public function getCountries(array $data)
    {
        return Country::query()->filterCountries($data['code'] ?? null, $data['banned'] ?? null, $data['name'] ?? null)->get();
    }

     public function getCountry(array $data)
    {
        return Country::query()->filterCountries($data['code'] ?? null, $data['banned'] ?? null, $data['name'] ?? null, $data['phone_code'] ?? null, $data['code_iso3'] ?? null)->first();
    }

    public function getCountriesInISO3Codes()
    {
        /* @var Api $wallesterService */
        $wallesterService = resolve(Api::class);
        $countriesWallester = $wallesterService->getAllowedCardDeliveryCountries();
        $countries = array_map(function ($countryCode) {
            return strtolower($countryCode);
        }, $countriesWallester['country_codes']);
        return Country::query()->whereIn('code_ISO3', $countries)->pluck('name', 'code')->toArray();
    }

}

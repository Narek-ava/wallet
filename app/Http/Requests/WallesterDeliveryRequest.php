<?php

namespace App\Http\Requests;

use App\Enums\WallesterCardTypes;
use App\Services\CountryService;
use Illuminate\Validation\Rule;

class WallesterDeliveryRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /* @var CountryService $countryService */
        $countryService = resolve(CountryService::class);

        return [
            'first_name' => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'last_name' => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'country_code' => ['bail', 'required', 'string', Rule::in(array_keys($countryService->getCountriesInISO3Codes()))],
            'address1' => ['bail', 'required', 'string', 'max:45'],
            'address2' => ['bail', 'nullable', 'string', 'max:45'],
            'postal_code' => ['bail', 'required', 'string', 'max:45'],
            'city' => ['bail', 'required', 'string', 'max:45'],
            'type' => ['required', Rule::in(array_keys(WallesterCardTypes::NAMES))],
        ];
    }
}

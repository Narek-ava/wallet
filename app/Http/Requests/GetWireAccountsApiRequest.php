<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Country;
use Illuminate\Validation\Rule;

class GetWireAccountsApiRequest extends BaseRequest
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
        return [
            'currency' => ['nullable', 'string', Rule::in(Currency::FIAT_CURRENCY_NAMES)],
            'country' => ['nullable', 'string', Rule::in(array_keys(Country::getCountries(false)))],
            'accountType' => ['nullable', 'string', Rule::in(array_keys(AccountType::ACCOUNT_WIRE_TYPES))],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Models\Country;
use Illuminate\Validation\Rule;

class GetProvidersByApiRequest extends BaseRequest
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
            'currency' => ['required', 'string', Rule::in(Currency::FIAT_CURRENCY_NAMES)],
            'country' => ['required', 'string', Rule::in(array_keys(Country::getCountries(false)))],
            'wireType' => ['required', 'string', Rule::in([OperationOperationType::TYPE_TOP_UP_SEPA, OperationOperationType::TYPE_TOP_UP_SWIFT, OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT])],
            'fiatType' => ['required', 'string', Rule::in([AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT, AccountType::PAYMENT_PROVIDER_FIAT_TYPE_FIAT])],
        ];
    }
}

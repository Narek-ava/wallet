<?php

namespace App\Http\Requests\API\v1;

use App\Enums\Currency;
use App\Enums\OperationOperationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderByCountryRequest extends FormRequest
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
            'wire_type' => ['required', 'integer', Rule::in(OperationOperationType::ALL_SWIFT_SEPA_TYPES)],
            'country' => ['required', 'integer', Rule::in(OperationOperationType::ALL_SWIFT_SEPA_TYPES)],
            'currency' => ['required', 'string', Rule::in(Currency::FIAT_CURRENCY_NAMES)],
        ];
    }
}

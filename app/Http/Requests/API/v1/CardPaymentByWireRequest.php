<?php

namespace App\Http\Requests\API\v1;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardPaymentByWireRequest extends FormRequest
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
            'wallesterAccountId'=>['required', 'string', 'exists:accounts,id'],
            'providerAccountId'=>['bail', 'required', 'string', 'exists:accounts,id'],
            'currency'=>['bail', 'required', 'string', Rule::in(Currency::FIAT_CURRENCY_NAMES)]
        ];

    }
}

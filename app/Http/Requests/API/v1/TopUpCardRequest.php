<?php

namespace App\Http\Requests\API\v1;

use App\Enums\Currency;
use App\Http\Requests\BaseRequest;
use App\Rules\CardOperationAmountRule;
use Illuminate\Validation\Rule;

class TopUpCardRequest extends BaseRequest
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
        $amount = $this->request->get('amount');
        $currency = $this->request->get('currency');

        $rules = [
            'wallet_id' => ['required', 'string'],
            'currency' => ['required', 'string', Rule::in(Currency::FIAT_CURRENCY_NAMES)],
            'amount' => ['required','numeric','min:0']
        ];

        if (!empty($currency) && in_array($currency, Currency::FIAT_CURRENCY_NAMES) && !empty($amount) && is_numeric($amount)) {
            $rules['amount'][] = new CardOperationAmountRule($amount, $currency);
        }

        return $rules;

    }
}

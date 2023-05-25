<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Validation\Rule;

class WallesterOrderWirePaymentRequest extends BaseRequest
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
            'id' => ['required', 'string', 'exists:accounts,id'],
            'provider_account_id' => ['bail', 'required', 'string', 'exists:accounts,id'],
            'currency' => ['bail', 'required', 'string', Rule::in(Currency::FIAT_CURRENCY_NAMES)]
        ];
    }
}

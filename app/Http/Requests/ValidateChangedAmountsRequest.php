<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ValidateChangedAmountsRequest extends FormRequest
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
            'amount' => ['bail', 'required', 'numeric', 'gt:0'],
            'cryptocurrency' => ['bail', 'required', 'string', Rule::in(Currency::getList())],
            'fromCryptoToFiat' => ['bail', 'required']
        ];
    }

    public function attributes()
    {
        return [
            'amount' => $this->fromCryptoToFiat === 'false' ? 'amount in euro' : 'amount'
        ];
    }
}

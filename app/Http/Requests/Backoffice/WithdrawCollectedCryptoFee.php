<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\Currency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WithdrawCollectedCryptoFee extends FormRequest
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
        $rules = [
            'project_id' => ['bail', 'required', 'string', 'exists:projects,id'],
            'amount' => ['bail', 'required', 'numeric', 'gt:0'],
            'toAddress' => ['bail', 'required', 'string'],
            'currency' => ['bail', 'required', Rule::in(Currency::getAllCurrencies())],
        ];

        if ($this->currency) {
            $rules['checkedTransactions' . $this->currency] = ['bail' ,'required', 'array', 'min:1'];
            $rules['checkedTransactions' . $this->currency . '.*'] = ['bail', 'required', 'string'];
        }

        return $rules;
    }
}

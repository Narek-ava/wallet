<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class WithdrawToFiatRequest extends BaseRequest
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
        $cProfile = getCProfile();
        return [
            'amount' => ['bail', 'required', 'numeric', 'gt:0'],
            'currency' => ['bail', 'required', 'string', Rule::in($cProfile->getFiatWallets()->pluck('id')->toArray())],
            'operation_id' => ['required', 'string'],
        ];
    }
}

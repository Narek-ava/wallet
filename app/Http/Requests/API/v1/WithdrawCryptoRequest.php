<?php

namespace App\Http\Requests\API\v1;

use App\Http\Requests\BaseRequest;
use App\Rules\ValidateAccountBalance;
use Illuminate\Validation\Rule;

class WithdrawCryptoRequest extends BaseRequest
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
        $cProfileId = auth()->user()->cProfile->id ?? '';
        $rules = [
            'from_wallet' => ['bail', 'required', Rule::exists('accounts', 'id')->where(function ($query) use($cProfileId) {
                return $query->where([
                    'c_profile_id' => $cProfileId,
                    'is_external' => 0
                ]);
            }), new ValidateAccountBalance($this->get('from_wallet') ?? '')],
            'amount' => ['bail', 'required', 'numeric', 'gt:0'],
            'to_crypto_account' => ['bail', 'required',
                Rule::exists('accounts', 'id')->where(function ($query) use($cProfileId) {
                return $query->where([
                    'c_profile_id' => $cProfileId,
                    'is_external' => 1
                ]);
            })],
        ];

        if(auth()->user()->two_fa_type){
            $rules['twoFaToken'] = ['required', 'string'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'from_wallet.exists' => t('withdrawal_account_not_found'),
            'to_crypto_account.exists' => t('withdrawal_account_not_found')
        ];
    }
}

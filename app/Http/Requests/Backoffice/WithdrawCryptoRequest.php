<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\TransactionType;
use App\Rules\BCHAddressFormat;
use Illuminate\Foundation\Http\FormRequest;


class WithdrawCryptoRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'gt:0'],
            'to_wallet' => ['required', new BCHAddressFormat($this->wallet_address, false)],
            'cProfile_id' => ['required','exists:c_profiles,id'],
            'crypto_account_detail_id' => ['required', 'exists:crypto_account_details,id'],
        ];
    }
}

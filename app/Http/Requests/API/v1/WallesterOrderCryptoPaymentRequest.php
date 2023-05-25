<?php

namespace App\Http\Requests\API\v1;

use App\Rules\CheckArrayElementsRule;
use Illuminate\Foundation\Http\FormRequest;

class WallesterOrderCryptoPaymentRequest extends FormRequest
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
            'bankCardId' => ['required', 'string', 'exists:accounts,id'],
            'fromCryptoWalletId' => ['required', 'string', 'exists:crypto_account_details,id'],
        ];
    }

    public function messages()
    {
        return [
            'fromWallet.required' => 'Invalid wallet provided',
            'fromWallet.string' => 'Invalid wallet provided',
            'fromWallet.exists' => 'Invalid wallet provided',
        ];
    }

}

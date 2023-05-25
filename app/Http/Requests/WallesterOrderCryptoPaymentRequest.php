<?php

namespace App\Http\Requests;


class WallesterOrderCryptoPaymentRequest extends BaseRequest
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
            'fromWallet' => ['required', 'string', 'exists:crypto_account_details,id']
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

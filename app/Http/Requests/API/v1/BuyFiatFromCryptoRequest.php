<?php

namespace App\Http\Requests\API\v1;

use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Http\Requests\BaseRequest;
use App\Rules\ValidateAccountBalance;
use Illuminate\Validation\Rule;

class BuyFiatFromCryptoRequest extends BaseRequest
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
            'fiat_wallet_id' => ['bail', 'required', 'string', Rule::in($cProfile->getFiatWallets()->pluck('id')->toArray())],
            'crypto_account_id' => ['bail', 'required', 'string', Rule::in($cProfile->accounts()->where(['account_type' => AccountType::TYPE_CRYPTO])->pluck('id')->toArray())],
        ];
    }
}

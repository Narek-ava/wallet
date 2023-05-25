<?php

namespace App\Http\Requests\API\v1;

use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Http\Requests\BaseRequest;
use App\Rules\ValidateAccountBalance;
use Illuminate\Validation\Rule;

class WithdrawWireRequest extends BaseRequest
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

        $return =  [
            'from_wallet' => ['bail', 'required',
                Rule::exists('accounts', 'id')->where(function ($query) use ($cProfileId) {
                    return $query->where([
                        'c_profile_id' => $cProfileId,
                        'is_external' => 0
                    ]);
                }),
                new ValidateAccountBalance($this->get('from_wallet') ?? '')],
            'amount' => ['bail', 'required', 'numeric', 'gt:0'],
            'to_wire_account' => ['bail', 'required',
                Rule::exists('accounts', 'id')->where(function ($query) use ($cProfileId) {
                    return $query->where([
                        'c_profile_id' => $cProfileId,
                        'is_external' => 1,
                    ]);
                })],
            'provider_id' => ['required', 'string',
                Rule::exists('accounts', 'id')->where(function ($query) {
                    return $query->where([
                        'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                    ])->whereNotNull('payment_provider_id');
                })
            ],
            'wire_type' => ['required', Rule::in(OperationOperationType::API_WIRE_TYPES)],
        ];

        if(auth()->user()->two_fa_type){
            $return['twoFaToken'] = ['required', 'string'];
        }

        return $return;

    }

    public function messages()
    {
        return [
            'from_wallet.exists' => t('withdrawal_account_not_found'),
            'to_wire_account.exists' => t('withdrawal_account_not_found'),
            'provider_id.exists' => t('provider_account_not_found')
        ];
    }
}

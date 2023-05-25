<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Models\ClientSystemWallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSystemWalletRequest extends FormRequest
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

        if ($this->method() == 'PUT') {
            $wallet = ClientSystemWallet::find($this->route('client_wallet'));
            $projectId = $wallet->project->id ?? null;
        } else {
            $projectId = $this->route()->parameter('projectId');
        }

        return [
            'walletId' => ['required', 'string'],
            'passphrase' => ['nullable', 'string'],
            'currency' =>  [
                'required',
                Rule::in(Currency::getList()),
                Rule::unique('client_system_wallets')
                    ->where('currency', $this->currency)
                    ->where('project_id', $projectId)
                    ->ignore($this->route('client_wallet'))
            ],
        ];
    }
}

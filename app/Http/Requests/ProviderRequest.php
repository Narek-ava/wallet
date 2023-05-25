<?php

namespace App\Http\Requests;

use App\Enums\ExchangeApiProviders;
use App\Services\ProviderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderRequest extends FormRequest
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
            'name' => ['bail', 'required', 'unique:payment_providers,name,' . $this->provider_id , "regex:/^[a-zA-Z].*([.'`0-9\p{Latin}]+[\ \-]?)+[a-zA-Z0-9 ]+$/"],
            'status' => "required",
        ];

        if ($this->providerType &&  in_array($this->providerType,[ 'liquidity-providers', 'credit-card-providers', 'card-issuing-providers', 'wallet-providers'])) {
            $rules['api'] = ['bail', 'required', 'string'];
            $rules['api_account'] = ['bail', 'required', 'string'];

            if ($this->providerType == 'card-issuing-providers') {
                $rules['plastic_card_amount']  = ['bail', 'required', 'numeric'];
                $rules['virtual_card_amount']  = ['bail', 'required', 'numeric'];
            }
        }

        return $rules;
    }
}

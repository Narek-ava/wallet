<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\PaymentFormStatuses;
use App\Models\Cabinet\CProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePaymentCryptoFormRequest extends FormRequest
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

    public function rules()
    {

        $rules = [
            'paymentFormProject' => 'required|string|exists:projects,id',
            'paymentFormName' => ['required', 'string', 'unique:payment_forms,name'],
            'paymentFormStatus' => ['required', 'int', Rule::in(array_keys(PaymentFormStatuses::NAMES))],
            'paymentFormMerchant' => ['required', 'string', 'exists:c_profiles,id'],
            'paymentFormWebSiteUrl' => ['required', 'string', 'url'],
            'paymentFormDescription' => ['required', 'string'],
            'paymentFormMerchantLogo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg|max:2048'],
            'paymentFormWalletProvider' => ['required', 'string', 'exists:payment_providers,id'],
            'paymentFormLiquidityProvider' => ['required', 'string', 'exists:payment_providers,id'],
            'paymentFormCardProvider' => ['nullable', 'string'],
            'paymentFormIncomingFee' => ['required', 'numeric'],
        ];

        if ($this->paymentFormCardProvider) {
            $rules['paymentFormCardProvider'] = array_merge($rules['paymentFormCardProvider'], ['exists:payment_providers,id']);
        }

        return $rules;
    }

    public function attributes()
    {
        return [
          'paymentFormWebSiteUrl' => 'payment form website url'
        ];
    }
}

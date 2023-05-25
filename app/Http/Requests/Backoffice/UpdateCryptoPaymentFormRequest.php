<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\PaymentFormStatuses;
use App\Models\PaymentForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCryptoPaymentFormRequest extends FormRequest
{
    protected ?PaymentForm $paymentForm;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->paymentForm = $this->route('paymentForm');

        return (bool) $this->paymentForm->id;
    }

    public function rules()
    {
        $rules = [];
        if (!$this->paymentForm->project_id) {
            $rules['paymentFormProject'] = ['string', 'required', 'exists:projects,id'];
        }

        return array_merge([
            'paymentFormName' => 'required|string|unique:payment_forms,name,' . $this->paymentForm->id,
            'paymentFormStatus' => ['required', 'int', Rule::in(array_keys(PaymentFormStatuses::NAMES))],
            'paymentFormMerchant' => 'required|string|exists:c_profiles,id',
            'paymentFormWalletProvider' => 'required|string|exists:payment_providers,id',
            'paymentFormWebSiteUrl' => 'required|string|url',
            'paymentFormDescription' => 'required|string',
            'paymentFormMerchantLogo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'paymentFormIncomingFee' => 'required|numeric',
            'paymentFormProject' => 'required|string|exists:projects,id',
        ], $rules);
    }

}

<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentFormRequest extends FormRequest
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
            'paymentFormName' => 'required|string|unique:payment_forms,name',
            'paymentFormStatus' => 'required|int',
            'paymentFormType' => 'required|int',
            'paymentFormCardProvider' => 'required|string',
            'paymentFormLiquidityProvider' => 'required|string',
            'paymentFormWalletProvider' => 'required|string',
            'paymentFormKYC' => 'required|int',
        ];

        if (in_array($this->paymentFormType, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rules['paymentFormRate'] = 'required|string';
        } else {
            $rules['paymentFormMerchant'] = 'required|string';
        }

        if($this->paymentFormType == PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM) {
            $currencyValidationKeys = array_map(function ($item) {
                return 'address_' . $item;
            },  Currency::getList());

            foreach (Currency::getList() as $currency) {
                $key = 'address_' . $currency;
                $rules[$key] = [
                    'required_without_all:' . implode(',', array_filter($currencyValidationKeys, function ($item) use ($key) {return $item !== $key;})),
                    'string',
                    'nullable'
                ];
            }
        }

        return $rules;
    }

    public function messages()
    {
        foreach (Currency::getList() as $k => $currency) {
            $key = 'address_' . $currency;
            $array = Currency::getList();
            unset($array[$k]);
            $values = implode('/', $array);
            $messages[$key.'.required_without_all'] = t('form_field_required_without_all', ['attribute' => $currency, 'values' => $values]);
        }

        return $messages;
    }
}

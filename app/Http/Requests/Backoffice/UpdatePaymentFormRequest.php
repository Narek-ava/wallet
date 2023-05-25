<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use App\Models\PaymentForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UpdatePaymentFormRequest extends FormRequest
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
        $rules = [
            'paymentFormName' => 'required|string|unique:payment_forms,name,' . $this->paymentForm->id,
            'paymentFormStatus' => 'required|int',
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

        if (!$this->paymentForm->project_id) {
            $rules['paymentFormProject'] = ['string', 'required', 'exists:projects,id'];
        }

        if (!$this->paymentForm->hasOperations()) {
            $rules['paymentFormType'] = ['int', 'required', Rule::in(PaymentFormTypes::AVAILABLE_FORM_TYPES)];
            if (!isset($rules['paymentFormProject'])) {
                $rules['paymentFormProject'] = ['string', 'nullable', 'exists:projects,id'];

            }
        }

        if ($this->paymentFormType == PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM) {
            $currencyValidationKeys = array_map(function ($item) {
                return 'address_' . $item;
            }, Currency::getList());

            foreach (Currency::getList() as $currency) {
                $key = 'address_' . $currency;
                $rules[$key] = [
                    'required_without_all:' . implode(',', array_filter($currencyValidationKeys, function ($item) use ($key) {
                        return $item !== $key;
                    })),
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

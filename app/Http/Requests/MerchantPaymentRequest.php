<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use App\Rules\NoEmojiRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class MerchantPaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'paymentFormAmount' => 'required|numeric',
            'currency' => ['required', 'string', \Illuminate\Validation\Rule::in(Currency::FIAT_CURRENCY_NAMES)],
            'cryptoCurrency' => ['required', 'string', \Illuminate\Validation\Rule::in(Currency::getList())],
            'paymentFormAttemptId' => ['required', 'string'],
        ];
        if ($this->paymentForm->type == PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM) {
            $rules['paymentFormToWalletAddress'] = 'required|string';
        }
        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                if ($validator->errors()->has('phone_cc_part') || $validator->errors()->has('phone_no_part') ) {
                    $validator->errors()->add('step', 2);
                }else if ($validator->errors()->has('paymentFormEmail')) {
                    $validator->errors()->add('step', 3);
                }
            }
        });
    }

    function failedValidation(Validator $validator)
    {
        logger()->error('Payment Form operation validation error', $validator->errors()->toArray());
        throw (new HttpResponseException(response(t('error_unknown'), 403)));
    }
}

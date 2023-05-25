<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use App\Rules\NoEmojiRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentFormRequest extends FormRequest
{

    const ACTION_VALIDATE_INITIAL = 'save_initial_form';
    const ACTION_VALIDATE_PHONE = 'validate_phone';
    const ACTION_VALIDATE_PHONE_CODE = 'validate_phone_code';
    const ACTION_VALIDATE_EMAIL = 'validate_email';
    const ACTION_VALIDATE_EMAIL_CODE = 'validate_email_code';
    const ACTION_CHECK_WALLET_ADDRESS = 'check_wallet_address';
    const ACTION_LOGIN_USER = 'login_user';
    const ACTION_GET_COMPLIANCE_DATA = 'get_compliance_data';
    const ACTION_VALIDATE_FORM = 'validate_form';

    const ACTIONS = [
        self::ACTION_VALIDATE_INITIAL,
        self::ACTION_VALIDATE_PHONE,
        self::ACTION_VALIDATE_PHONE_CODE,
        self::ACTION_VALIDATE_EMAIL,
        self::ACTION_VALIDATE_EMAIL_CODE,
        self::ACTION_CHECK_WALLET_ADDRESS,
        self::ACTION_LOGIN_USER,
        self::ACTION_GET_COMPLIANCE_DATA,
        self::ACTION_VALIDATE_FORM,
    ];

    public function rules()
    {
        $rules['action'] = [Rule::in(self::ACTIONS)];

        if ($this->action != self::ACTION_VALIDATE_INITIAL) {
            $rules['paymentFormAttemptId'] = ['required', 'string'];
        }

        switch ($this->get('action')) {
            case self::ACTION_VALIDATE_INITIAL:
                $rules['paymentFormAmount'] = ['required', 'numeric'];
                $rules['currency'] = ['required', 'string', \Illuminate\Validation\Rule::in(Currency::FIAT_CURRENCY_NAMES)];
                $rules['cryptoCurrency'] = ['required', 'string', \Illuminate\Validation\Rule::in(Currency::getList())];
                $rules['paymentFormId'] = ['required', 'string'];
                $rules['first_name'] = ['required', 'string'];
                $rules['last_name'] = ['required', 'string'];
                break;
            case self::ACTION_VALIDATE_EMAIL:
                $rules['email'] = ['required','string', 'email', new NoEmojiRule, 'max:100', 'min:2'];
                break;
            case self::ACTION_VALIDATE_PHONE:
                $rules['phone_cc_part'] = ['required','numeric', new NoEmojiRule];
                $rules['phone_no_part'] = ['required','numeric', new NoEmojiRule];
                break;
            case self::ACTION_VALIDATE_EMAIL_CODE:
                $rules['email'] = ['required','string', 'email', new NoEmojiRule, 'max:100', 'min:2'];
                $rules['code'] = ['required', 'digits:' . \C\SMS_SIZE];
                break;
            case self::ACTION_CHECK_WALLET_ADDRESS:
                $rules['wallet_address'] = ['required','string', new NoEmojiRule];
                break;
            case self::ACTION_LOGIN_USER:
                $rules['paymentFormUserPassword'] = ['required'];
                break;
            case self::ACTION_VALIDATE_FORM:
                $rules['paymentFormId'] = ['required', 'string'];
                break;
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
}

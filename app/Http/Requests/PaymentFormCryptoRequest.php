<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Rules\NoEmojiRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentFormCryptoRequest extends FormRequest
{
    const ACTION_VALIDATE_INITIAL = 'save_initial_form';
    const ACTION_VALIDATE_PHONE = 'validate_phone';
    const ACTION_VALIDATE_PHONE_CODE = 'validate_phone_code';
    const ACTION_VALIDATE_EMAIL = 'validate_email';
    const ACTION_VALIDATE_EMAIL_CODE = 'validate_email_code';
    const ACTION_CHECK_WALLET_ADDRESS = 'check_wallet_address';
    const ACTION_LOGIN_USER = 'login_user';
    const ACTION_GET_COMPLIANCE_DATA = 'get_compliance_data';

    const ACTIONS = [
        self::ACTION_VALIDATE_INITIAL,
        self::ACTION_VALIDATE_PHONE,
        self::ACTION_VALIDATE_PHONE_CODE,
        self::ACTION_VALIDATE_EMAIL,
        self::ACTION_VALIDATE_EMAIL_CODE,
        self::ACTION_CHECK_WALLET_ADDRESS,
        self::ACTION_LOGIN_USER,
        self::ACTION_GET_COMPLIANCE_DATA
    ];

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
        $rules['action'] = [Rule::in(self::ACTIONS)];

        $requiredOrNullable = $this->action != self::ACTION_VALIDATE_INITIAL ? 'required' : 'nullable';
        $rules['paymentFormAttemptId'] = [$requiredOrNullable, 'string'];

        switch ($this->get('action')) {
            case self::ACTION_VALIDATE_INITIAL:
                $rules['paymentFormAmount'] = ['required', 'numeric'];
                $rules['cryptoCurrency'] = ['required', 'string', Rule::in(Currency::getList())];
                $rules['currency'] = ['required', 'string', Rule::in(Currency::getList())];
                $rules['paymentFormId'] = ['required', 'string'];
                break;
            case self::ACTION_VALIDATE_EMAIL:
                $rules['email'] = ['required','string', 'email', new NoEmojiRule, 'max:100', 'min:2'];
                break;
            case self::ACTION_VALIDATE_PHONE:
                $rules['phone_cc_part'] = ['required','numeric', new NoEmojiRule];
                $rules['phone_no_part'] = ['required','numeric', new NoEmojiRule];
                break;
        }

        return $rules;
    }
}

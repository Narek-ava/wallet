<?php

namespace App\Http\Requests\Cabinet\API\v1;


class ConfirmPhoneCodeRequest extends VerifyPhoneRequest
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
            'code' => ['required', 'digits:' . \C\SMS_SIZE],
        ];
        return array_merge(parent::rules(), $rules);
    }

    public function messages()
    {
        return [
            'code.required' => t('error_sms_code_required'),
            'code.*' => t('error_sms_code_format'),
        ];
    }
}

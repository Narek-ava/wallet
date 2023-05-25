<?php

namespace App\Http\Requests\Cabinet\API\v1;

class ConfirmVerifyEmailCode extends CUserRegisterSmsRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:c_users,email'],
        ]);
    }
}

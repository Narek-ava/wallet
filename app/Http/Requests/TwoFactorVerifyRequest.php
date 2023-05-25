<?php

namespace App\Http\Requests;

class TwoFactorVerifyRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => ['required', 'regex:/^[0-9]{6}+$/',],
            'twoFaId' => ['required', 'string'],
        ];
    }


}

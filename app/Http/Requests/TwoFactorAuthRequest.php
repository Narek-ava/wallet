<?php

namespace App\Http\Requests;

class TwoFactorAuthRequest extends BaseRequest
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
        ];
    }


}

<?php

namespace App\Http\Requests;

class TokenRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'refreshToken' => 'required',
        ];
    }

}

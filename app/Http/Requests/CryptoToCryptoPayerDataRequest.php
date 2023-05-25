<?php

namespace App\Http\Requests;


use App\Http\Requests\Cabinet\API\v1\VerifyPhoneRequest;

class CryptoToCryptoPayerDataRequest extends VerifyPhoneRequest
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
        return array_merge(parent::rules(), [
            'first_name' => ['bail', 'required', 'string', 'min:3', 'max:50', "regex:/^[a-zA-Z]+((['. -][a-zA-Z ])?[a-zA-Z]*)*$/"],
            'last_name' => ['bail', 'required', 'string', 'min:3', 'max:50', "regex:/^[a-zA-Z]+((['. -][a-zA-Z ])?[a-zA-Z]*)*$/"],
            'email' => ['bail', 'required', 'string', 'email'],
        ]);
    }
}

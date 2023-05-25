<?php

namespace App\Http\Requests\Cabinet\API\v1;


use App\Models\Cabinet\CProfile;
use App\Rules\Password;
use Illuminate\Validation\Rule;

class RegisterCUserAPIRequest extends \App\Http\Requests\BaseRequest
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
        return [
            'account_type' => ['required', Rule::in([CProfile::TYPE_INDIVIDUAL, CProfile::TYPE_CORPORATE])],
            'phone_verification_token' => ['required', 'string'],
            'email_verification_token' => ['required', 'string'],
            'password' => ['required', new Password()],
            'ref' => ['nullable', 'string'],
        ];
    }
}

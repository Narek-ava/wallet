<?php

namespace App\Http\Requests\Cabinet\API\v1;


use App\Enums\Country;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class VerifyEmailRequest extends \App\Http\Requests\BaseRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:c_users,email'],
        ];
    }

}

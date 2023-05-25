<?php

namespace App\Http\Requests\Cabinet\API\v1;


use App\Enums\Country;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class VerifyPhoneRequest extends \App\Http\Requests\BaseRequest
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
        $returnData = \C\PHONE_RULES;
        $returnData['phone_cc_part'][] = Rule::in(Arr::flatten(Country::COUNTRY_CODE_FOR_PHONE));

        return $returnData;
    }

    public function attributes()
    {
        return [
            'phone_cc_part' => 'Country code',
            'phone_no_part' => 'Phone number',
        ];
    }
}

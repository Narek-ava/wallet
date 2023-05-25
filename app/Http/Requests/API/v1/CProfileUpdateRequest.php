<?php

namespace App\Http\Requests\API\v1;

use App\Enums\Gender;
use App\Http\Requests\Cabinet\API\v1\VerifyPhoneRequest;
use App\Models\Country;
use App\Rules\EnglishWithSpecialChars;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class CProfileUpdateRequest extends \App\Http\Requests\BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $cUser = auth()->user();

        $rules = [
            'first_name' => ['bail', 'required', 'string', 'max:255','regex:/^[a-zA-Z ]+$/u'],
            'last_name' => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'country' => ['bail', 'required', 'string', 'max:50', 'regex:/^[a-zA-Z ]+$/u', Rule::in(array_keys(Country::getCountries(false))) ],

        ];

        if ($cUser->cProfile->compliance_level >= 1) {

            $rules = array_merge($rules, [
                'phone' => ['bail', 'required', 'string', 'min:5', 'max:15', 'regex:/^([0-9]*)$/', 'unique:c_users,phone,' . $cUser->id],
                'city' => ['bail', 'required', 'string', 'max:50', 'regex:/^[a-zA-Z- ]+$/u'],
                'citizenship' => ['bail', 'required', 'string', 'max:50', 'regex:/^[a-zA-Z ]+$/u'],
                'zip_code' => ['bail', 'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/u'],
                'address' => ['bail', 'required', 'string', 'max:200', new EnglishWithSpecialChars()],
                'date_of_birth' => ['bail', 'required', 'date:Y-m-d', function ($attribute, $value, $fail) {
                    if (now()->subYears(17)->lt(Carbon::parse($this->date_of_birth))) {
                        return $fail(t('ui_error_age_error'));
                    }
                }],
            ]);
        }


        return $rules;
    }
}

<?php

namespace App\Http\Requests\Backoffice;

use App\Models\Cabinet\CProfile;
use App\Models\Country;
use App\Rules\EnglishWithSpecialChars;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CProfileStoreRequest extends FormRequest
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
            //cUser fields
            'phone' => ['string', 'min:5', 'max:15', 'unique:c_users', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:c_users'],
            'password' => [new Password()],

            //cProfile fields
            'account_type' => ['required', 'between:' . CProfile::TYPE_INDIVIDUAL . ',' . CProfile::TYPE_CORPORATE],
            'first_name' => ['required', 'string', 'max:255','regex:/^[a-zA-Z ]+$/u'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'country' => ['bail', 'string','nullable', Rule::in(array_keys(Country::getCountries(false)))],
            'manager_id' =>  ['string', 'nullable', 'exists:b_users,id'],
            'compliance_officer_id' =>  ['string', 'nullable', 'exists:b_users,id'],
            'project_id' => ['bail', 'required', 'exists:projects,id'],
        ];
    }

    public function attributes()
    {
        return [
            'project_id' => 'project'
        ];
    }
}

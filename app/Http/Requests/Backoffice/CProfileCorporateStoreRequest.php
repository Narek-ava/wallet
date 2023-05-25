<?php

namespace App\Http\Requests\Backoffice;

use App\Models\Cabinet\CProfile;
use App\Models\Country;
use App\Rules\EnglishWithSpecialChars;
use App\Rules\LinkedinLink;
use App\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CProfileCorporateStoreRequest extends FormRequest
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
            'phone' => ['string','min:5', 'max:15', 'unique:c_users', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:c_users'],
            'password' =>  [new Password()],

            //cProfile fields
            'manager_id' =>  ['string', 'nullable', 'exists:b_users,id'],
            'compliance_officer_id' =>  ['string', 'nullable', 'exists:b_users,id'],
            'company_name' => ['string', 'required', 'max:150', new EnglishWithSpecialChars()],
            'country' => ['bail', 'string','nullable', Rule::in(array_keys(Country::getCountries(false)))],
            "beneficial_owners.*"  => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
            'interface_language' => ['string', 'nullable',  'max:2'],
            'currency_rate' => ['string', 'nullable',  'max:3'],
            'day' => ['numeric', 'nullable', 'max:31'],
            'month' => ['numeric', 'nullable', 'max:12'],
            'year' => ['numeric', 'nullable', 'min:1920',  function ($attribute, $value, $fail) {
                $fullDate = $this->year.'-'.$this->month.'-'.$this->day;
                if(date('Y-m-d', strtotime($fullDate)) != $fullDate){
                    return $fail(t('ui_error_wrong_date'));
                }
                if(strtotime($fullDate) > time()){
                    return $fail(t('ui_error_date_from_future'));
                }
            }],
            'project_id' => ['bail', 'required', 'exists:projects,id'],
        ];
    }

    public function attributes()
    {
        return [
            'ceos.*' => 'ceo',
            'beneficial_owners.*' => 'beneficial owner',
            'shareholders.*' => 'shareholder',
            'project_id' => 'project'
        ];
    }
}

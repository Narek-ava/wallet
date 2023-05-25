<?php

namespace App\Http\Requests\Common;

use App\Enums\Gender;
use App\Http\Requests\BaseRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Country;
use App\Rules\EnglishWithSpecialChars;
use Illuminate\Validation\Rule;

class CProfileUpdateRequest extends BaseRequest
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
        if ($this->isMethod('patch')) {
            if (isRouteFromBackoffice()) {
                $profile = CProfile::where('id', $this->route()->parameter('profileId'))->firstOrFail();
                $cUser = $profile->cUser;
            } else {
                $cUser = auth()->user();
            }
        }
        $cUserId = $cUser->id ?? null;

        $rules = [
            'first_name' => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'last_name' => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'country' => ['bail', 'required', 'string', Rule::in(array_keys(Country::getCountries(false)))],
        ];

        if ($cUser->cProfile->compliance_level >= 1 || !$cUser->cProfile->cUser->project->complianceProvider()) {
            $phoneRules = ['bail', 'required', 'string', 'min:5', 'max:15', 'regex:/^([0-9]*)$/'];
            $phoneRules[] = 'unique:c_users,phone,' . $cUserId;
            $rules = array_merge($rules, [
                //cUser fields
                'phone' => $phoneRules,

                //cProfile fields

                'city' => ['bail', 'required', 'string', 'max:50', 'regex:/^[a-zA-Z- ]+$/u'],
                'citizenship' => ['bail', 'required', 'string', Rule::in(array_keys(Country::getCountries(false)))],
                'zip_code' => ['bail', 'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/u'],
                'address' => ['bail', 'required', 'string', 'max:200', new EnglishWithSpecialChars()],
                'day' => ['bail', 'required', 'numeric', 'max:31'],
                'month' => ['bail', 'required', 'numeric', 'max:12'],
                'year' => ['bail', 'required', 'numeric', 'min:1920', function ($attribute, $value, $fail) {
                    $fullDate = $this->year . '-' . $this->month . '-' . $this->day;
                    if (date('Y-m-d', strtotime($fullDate)) != $fullDate) {
                        return $fail(t('ui_error_wrong_date'));
                    }
                    if (strtotime('-17 years') < strtotime($fullDate)) {
                        return $fail(t('ui_error_age_error'));
                    }
                }],
                'gender' => ['bail', 'required', Rule::in(array_keys(Gender::NAMES))],
                'passport' => ['bail', 'required', 'string','min:5', 'max:20', 'regex:/^[A-Za-z0-9]+$/u'],
            ]);
        }

        return $rules;
    }
}

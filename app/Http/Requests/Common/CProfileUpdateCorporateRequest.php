<?php

namespace App\Http\Requests\Common;

use App\Enums\Industry;
use App\Enums\LegalForm;
use App\Http\Requests\BaseRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Country;
use App\Rules\EnglishWithSpecialChars;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CProfileUpdateCorporateRequest extends BaseRequest
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
                $cProfileId = $this->route()->parameter('profileId');
                $profile = CProfile::where('id', $cProfileId)->firstOrFail();
            } else {
                $profile = getCProfile();
                $cProfileId = $profile->id;
            }
            $cUser = $profile->cUser;
        }

        $cUserId = $cUser->id ?? null;

        $rules = [
            'company_name' => ['bail', 'required', 'string',  'max:150', new EnglishWithSpecialChars()],
            'country' => ['bail', 'required', 'string', 'max:50', Rule::in(array_keys(Country::getCountries(false)))],
        ];

        if ($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1 || !$profile->cUser->project->complianceProvider()) {
            $rules = array_merge($rules, [
                //corporate account fields
                'company_email' => ['bail', 'required', 'string', 'email', 'max:255'],
                'company_phone' => ['bail', 'required', 'string', 'min:5', 'max:15','regex:/^([0-9\s\-\+\(\)]*)$/'],
                'contact_phone' => ['bail', 'required', 'string', 'min:5', 'max:15','regex:/^([0-9]*)$/', 'unique:c_users,phone,' . $cUserId],
                'registration_number' => ['bail', 'required', 'string', 'regex:/^[A-Za-z0-9]+$/u',  'max:50'],
                'legal_address' => ['bail', 'required', 'string', 'max:200', new EnglishWithSpecialChars()],
                'trading_address' => ['bail', 'required', 'string', 'max:200', new EnglishWithSpecialChars()],
                'webhook_url' => ['bail', 'nullable', 'string', 'url'],
                "beneficial_owners"    => ['required', 'array', 'min:1'],
                "ceos"    => ['bail' ,'required', 'array', 'min:1'],
                "ceos.*"    => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
                "shareholders"    => ['bail' ,'required', 'array', 'min:1'],
                "shareholders.*"    => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
                "beneficial_owners.*"  => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
                'contact_email' => ['bail', 'required', 'email', 'max:200', 'unique:c_profiles,contact_email,'.$cProfileId],
                'interface_language' => ['bail', 'required', 'string', 'max:2'],
                'day' => ['bail', 'required', 'numeric', 'max:31'],
                'month' => ['bail', 'required', 'numeric', 'max:12'],
                // @todo CodeDup
                'year' => ['bail', 'required', 'numeric', 'min:1920', function ($attribute, $value, $fail) {
                    $fullDate = $this->year.'-'.$this->month.'-'.$this->day;
                    if(date('Y-m-d', strtotime($fullDate)) != $fullDate){
                        return $fail(t('ui_error_wrong_date'));
                    }
                    if(strtotime($fullDate) > time()){
                        return $fail(t('ui_error_date_from_future'));
                    }
                }],
            ]);
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'ceos.*' => 'ceo',
            'beneficial_owners.*' => 'beneficial owner',
            'shareholders.*' => 'shareholder',
        ];
    }
}

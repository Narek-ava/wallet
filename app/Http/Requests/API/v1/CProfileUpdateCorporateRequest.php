<?php

namespace App\Http\Requests\API\v1;

use App\Enums\Industry;
use App\Enums\LegalForm;
use App\Http\Requests\Cabinet\API\v1\VerifyPhoneRequest;
use App\Models\Country;
use App\Rules\EnglishWithSpecialChars;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CProfileUpdateCorporateRequest extends \App\Http\Requests\BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        $cUser = auth()->user();
        $cProfile = $cUser->cProfile;

        $rules = [
            'company_name' => ['bail', 'required', 'string', 'max:150', new EnglishWithSpecialChars()],
            'country' => ['bail', 'required', 'string', 'max:50', Rule::in(array_keys(Country::getCountries(false)))],

        ];

        if ($cProfile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1) {
            $rules = array_merge($rules, [
                //corporate account fields
                'company_email' => ['bail', 'required', 'string', 'email', 'max:255'],
                'company_phone' => ['bail', 'required', 'string', 'min:5', 'max:15', 'regex:/^([0-9\s\-\+\(\)]*)$/'],
                'contact_phone' => ['bail', 'required', 'string', 'min:5', 'max:15', 'regex:/^([0-9]*)$/', 'unique:c_users,phone,' . $cUser->id],
                'registration_number' => ['bail', 'required', 'string', 'regex:/^[A-Za-z0-9]+$/u', 'max:50'],
                'legal_address' => ['bail', 'required', 'string', 'max:200', new EnglishWithSpecialChars()],
                'trading_address' => ['bail', 'required', 'string', 'max:200', new EnglishWithSpecialChars()],
                'webhook_url' => ['bail', 'nullable', 'string', 'url'],
                "beneficial_owners" => ['required', 'array', 'min:1'],
                "ceos" => ['bail', 'required', 'array', 'min:1'],
                "ceos.*" => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
                "shareholders" => ['bail', 'required', 'array', 'min:1'],
                "shareholders.*" => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
                "beneficial_owners.*" => ['bail', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z ]+$/u'],
                'contact_email' => ['bail', 'required', 'email', 'max:200', 'unique:c_profiles,contact_email,' . $cProfile->id],
                'interface_language' => ['bail', 'required', 'string', 'max:2'],
                'registration_date' => ['date:Y-m-d', function ($attribute, $value, $fail) {
                    if (now()->lt(Carbon::parse($this->registration_date))) {
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

<?php

namespace App\Http\Requests\Backoffice;

use App\Enums\Currency;
use App\Enums\EmailProviders;
use App\Enums\ProjectStatuses;
use App\Enums\SmsProviders;
use App\Rules\Backoffice\CheckAllManagersToHaveRolesRule;
use App\Rules\Backoffice\CheckManagerRolesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
            //
        ];
        $rules = [
            'logo' =>  'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required|string|min:3|max:25|regex:/[a-zA-Z0-9]+/u',
            'domain' => ['required', 'string', 'min:3', Rule::unique('projects', 'domain')->where('status', ProjectStatuses::STATUS_ACTIVE)->ignore($this->route()->parameter('project'), 'id')],
            'status' => ['bail', 'required', 'int', Rule::in(array_keys(ProjectStatuses::NAMES))],
            'bUsers' => ['bail', 'required', 'array', 'min:1'],
            'bUsers.*' => ['bail', 'required', 'string', 'exists:b_users,id'],
            'roles' => ['bail', 'required', 'array', 'min:1', new CheckAllManagersToHaveRolesRule($this->bUsers ?? [])],
            'roles.*' => ['bail', 'required', 'array', 'min:1', new CheckManagerRolesRule()],

            'liqProvider' => ['bail', 'required', 'string', 'exists:payment_providers,id'],
            'issuingProvider' => ['bail', 'nullable', 'string', 'exists:payment_providers,id'],
            'walletProvider' => ['bail', 'required', 'string', 'exists:payment_providers,id'],
            'cardProvider' => ['bail', 'nullable', 'string', 'exists:payment_providers,id'],
            'complianceProvider' => ['bail', 'nullable', 'string', 'exists:compliance_providers,id'],

            'liquidityProviders' => ['bail', 'required', 'array'],
            'liquidityProviders.*' => ['bail', 'required', 'string', 'exists:payment_providers,id'],
            'paymentProviders' => ['bail', 'nullable', 'array'],
            'paymentProviders.*' => ['bail', 'nullable', 'string', 'exists:payment_providers,id'],


            'smsProviders' => ['bail', 'required',  'array', 'min:1'],
            'smsProviders.*' => ['bail', 'string', Rule::in(array_keys(SmsProviders::getList()))],

            'emailProvider' => ['bail', 'required','string', Rule::in(array_keys(config('mail.email_providers')))],

            'mainColor' => 'required|string|min:3',
            'buttonColor' => 'required|string|min:3',
            'borderColor' => 'required|string|min:3',
            'notifyFromColor' => 'required|string|min:3',
            'notifyToColor' => 'required|string|min:3',

            'individualRate' => ['required', 'exists:rate_templates,id'],
            'corporateRate' => ['required', 'exists:rate_templates,id'],
            'bankCardRate' => ['required', 'exists:bank_card_rate_templates,id'],

            'companyName' => ['bail', 'required', 'string', 'min:3', 'max:25' ],
            'companyCountry' => ['bail', 'required', 'string', 'min:3', 'max:25'],
            'companyCity' => ['bail', 'nullable', 'string',  'min:3', 'max:25'],
            'companyZipCode' => ['bail', 'nullable', 'string', 'min:3', 'max:25'],
            'companyAddress' => ['bail', 'nullable', 'string', 'min:3', 'max:100'],
            'companyLicense' => ['bail', 'nullable', 'string', 'min:3', 'max:25'],
            'companyRegistry' => ['bail', 'nullable', 'string', 'min:3', 'max:25'],

            'renewalInterval' => ['bail', 'nullable', 'integer', 'between:1,12'],

            'termsAndConditions' => ['bail', 'required', 'url'],
            'amlPolicy' => ['bail', 'required', 'url'],
            'privacyPolicy' => ['bail', 'required', 'url'],
            'frequentlyAskedQuestion' => ['bail', 'required', 'url'],
            'kytProvider' => ['bail', 'required',  'string', 'min:1'],
        ];

        if ($this->get('createClientWallet')) {
            $currencyValidationKeys = array_map(function ($item) {
                return 'walletId' . $item;
            },  Currency::getList());

            foreach (Currency::getList() as $currency) {
                $currencyKey = 'currency' . $currency;
                $rules['currency' . $currency] = ['bail','required', Rule::in(Currency::getList())];
                $rules[$currencyKey] = [
                    'required_without_all:' . implode(',', array_filter($currencyValidationKeys, function ($item) use ($currencyKey) {return $item !== $currencyKey;})),
                     Rule::in(Currency::getList()),
                    'nullable'
                ];

                $walletKey = 'walletId' . $currency;
                $rules[$walletKey] = [
                    'required_without_all:' . implode(',', array_filter($currencyValidationKeys, function ($item) use ($walletKey) {return $item !== $walletKey;})),
                    'string',
                    'nullable'
                ];
                $rules['passphrase' . $currency] = ['nullable', 'string'];

            }
        }

        return $rules;
    }


    public function attributes()
    {
        return [
            'bUsers' => 'available manager',
            'roles.*' => 'manager role',
            'roles.*.*' => 'manager role',
            'liqProvider' => 'default liquidity provider',
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        foreach (Currency::getList() as $currency) {
            $messages['walletId' . $currency . '.required_without_all'] = t('error_currency_required', ['currency' => $currency]);
        }

        return $messages;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($validator->errors()->has('roles')) {

            if (in_array('empty_roles', $validator->errors()->get('roles'))) {
                $keys = array_diff($this->bUsers, array_keys($this->roles));
                foreach ($keys as $key) {
                    $validator->errors()->add('roles.' . $key, t('roles_can_not_be_empty'));
                }
            } else {
                $roleErrors = $validator->errors()->get('roles');
                $validator->errors()->add('mainRoles', $roleErrors[0]);
            }
        }

        if ($validator->errors()->has('logo') || $validator->errors()->has('name') || $validator->errors()->has('termsAndConditions')
            || $validator->errors()->has('amlPolicy') || $validator->errors()->has('privacyPolicy') || $validator->errors()->has('frequentlyAskedQuestion')
            || $validator->errors()->has('domain') || $validator->errors()->has('address')) {
            $validator->errors()->add('generalSettings', true);
        }
        if ($validator->errors()->has('mainColor') || $validator->errors()->has('buttonColor')
            || $validator->errors()->has('borderColor') || $validator->errors()->has('notifyFromColor')
            || $validator->errors()->has('notifyToColor')
        ) {
            $validator->errors()->add('colorSetting', true);
        }

        if ($validator->errors()->has('individualRate') || $validator->errors()->has('corporateRate')) {
            $validator->errors()->add('rateSetting', true);
        }


        if ($validator->errors()->has('emailProvider') || $validator->errors()->has('smsProviders') || $validator->errors()->has('paymentProviders') || $validator->errors()->has('liquidityProviders') || $validator->errors()->has('cardProvider')
            || $validator->errors()->has('walletProvider') || $validator->errors()->has('issuingProvider') || $validator->errors()->has('liqProvider')
        ) {
            $validator->errors()->add('providerSetting', true);
        }

        if ($validator->errors()->has('roles') || $validator->errors()->has('bUsers')) {
            $validator->errors()->add('managerSetting', true);
        }

        foreach (Currency::NAMES as $currency) {
            if ($validator->errors()->has('walletId' . $currency) || $validator->errors()->has('passphrase' . $currency)) {
                $validator->errors()->add('clientWallets', true);
            }
        }

        parent::failedValidation($validator);
    }

}

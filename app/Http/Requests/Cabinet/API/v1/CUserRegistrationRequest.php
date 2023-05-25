<?php

namespace App\Http\Requests\Cabinet\API\v1;

use App\Models\Cabinet\CProfile;
use App\Rules\Password;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CUserRegistrationRequest extends \App\Http\Requests\BaseRequest
{

    public function rules()
    {
      $returnData = [
            'account_type' => ['required', 'between:' . CProfile::TYPE_INDIVIDUAL . ',' . CProfile::TYPE_CORPORATE],
            'email' => ['required', 'string', 'email', 'max:255'], // @note 'unique:c_users' excluded due error_duplicate_credentials
            'password' => new Password()
        ];

      if(!$this->expectsJson()) {
          $returnData['confirm_agreement'] = ['accepted'];
          $returnData['confirm_country'] = ['accepted'];
          if(config('cratos.age_confirmation') && $this->get('account_type') == CProfile::TYPE_INDIVIDUAL) {
              $returnData['age_confirmation_required'] = ['accepted'];
          }
      }

      return $returnData + \C\PHONE_RULES;
    }

    public function failedValidation(Validator $validator)
    {
        if($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Validation errors',
                'data'      => $validator->errors()
            ]));
        }

        parent::failedValidation($validator);
    }

    public function messages()
    {
        return [
            'account_type.*' => t('error_account_type'),
            'confirm_agreement.*' => t('error_confirm_agreement'),
            'confirm_country.*' => t('error_confirm_country'),
            'age_confirmation_required.*' => t('error_confirm_years'),
            'phone_cc_part.*' => t(\C\PHONE_ERROR_KEY),
            'phone_no_part.*' => t(\C\PHONE_ERROR_KEY),
        ];
    }

}

<?php

namespace App\Http\Requests;

use App\Enums\WallesterCardTypes;
use App\Models\WallesterAccountDetail;
use App\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;

class WallesterCardSecureUpdate extends BaseRequest
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
        $rules =  [
            'internet_purchases' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
            'overall_limits_enabled' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
            'password' => ['bail', 'required', 'confirmed', new PasswordRule()],
        ];

        if ($this->type && $this->type == WallesterCardTypes::TYPE_PLASTIC) {
            $rules['contactless_purchases'] = ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))];
            $rules['atm_withdrawals'] = ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))];
        }

        return $rules;
    }


    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                $validator->errors()->add('show_security_modal', true);
            }
        });
    }
}

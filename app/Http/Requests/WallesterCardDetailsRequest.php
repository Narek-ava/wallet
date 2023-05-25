<?php

namespace App\Http\Requests;


use App\Enums\WallesterCardTypes;
use App\Models\WallesterAccountDetail;
use App\Rules\CheckArrayElementsRule;
use App\Rules\Password as PasswordRule;
use App\Services\Wallester\Api;
use Illuminate\Validation\Rule;

class WallesterCardDetailsRequest extends BaseRequest
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
        $wallesterApi = resolve(Api::class);
        /* @var Api $wallesterApi */

        $defaultLimitsArray = $wallesterApi->getCardDefaultLimitsCached();

        $rules =  [
            'type' => ['required', Rule::in(array_keys(WallesterCardTypes::NAMES))],
            'internet_purchases' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
            'overall_limits_enabled' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
            'password' => ['bail', 'required', 'confirmed', new PasswordRule()],
            'password_confirmation' => ['bail', 'required'],
            "limits"    => ['bail' ,'required', 'array', 'min:12'],
            "limits.*"    => ['bail', 'required', 'numeric', 'gte:0', new CheckArrayElementsRule($this->limits, $defaultLimitsArray,'max_card_', '_limit')],
        ];

        if ($this->type && $this->type == WallesterCardTypes::TYPE_PLASTIC) {
            $rules['contactless_purchases'] = ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))];
            $rules['atm_withdrawals'] = ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))];
        }

        return $rules;
    }
}

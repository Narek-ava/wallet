<?php


namespace App\Http\Requests\Backoffice;


use App\Enums\TemplateType;
use App\Enums\WallesterCardOrderPaymentMethods;
use App\Enums\WallesterCardTypes;
use App\Models\WallesterAccountDetail;
use App\Rules\CheckArrayElementsRule;
use App\Rules\Password;
use App\Rules\Password as PasswordRule;
use App\Services\Wallester\Api;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WallesterCardRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        $this->redirect = "/backoffice/profile/{$this->cProfileId}#cards";

        $wallesterApi = resolve(Api::class);
        /* @var Api $wallesterApi */

        $defaultLimitsArray = $wallesterApi->getCardDefaultLimitsCached();

        $rules = [
            "cProfileId" => ['bail', 'required', 'exists:c_profiles,id'],
            "type" => ['bail', 'required', Rule::in(  array_keys(WallesterCardTypes::CARD_TYPES_LOWER))],
            "paymentMethod" => ['bail', 'required', Rule::in(  array_keys(WallesterCardOrderPaymentMethods::NAMES))],
            'internet_purchases' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
            'overall_limits_enabled' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
            'password' => ['bail', 'required', 'confirmed', new PasswordRule()],
            "limits"    => ['bail' ,'required', 'array', 'min:12'],
            "limits.*"    => ['bail', 'required', 'numeric', 'gte:0', new CheckArrayElementsRule($this->limits, $defaultLimitsArray,'max_card_', '')],
        ];



        if ($this->get('type') == WallesterCardTypes::TYPE_PLASTIC) {
            $rules = array_merge([
                'contactless_purchases' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
                'atm_withdrawals' => ['bail', 'required', 'int', Rule::in(array_keys(WallesterAccountDetail::SECURITY_YES_OR_NO))],
                "first_name" => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
                "last_name" => ['bail', 'required', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
                "address1" => ['bail', 'required', 'string', 'max:45',],
                "address2" => ['bail', 'nullable', 'string', 'max:45'],
                "postal_code" => ['bail', 'required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/u'],
                "city" => ['bail', 'required', 'string', 'max:25', 'regex:/^[a-zA-Z ]+$/u'],
                "country_code" => ['bail', 'required', 'string', 'max:25', 'regex:/^[a-zA-Z ]+$/u'],
            ], $rules);
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        if (!$this->wantsJson()) {
            $validator->after(function ($validator) {
                if ($validator->errors()->any()) {
                    $validator->errors()->add('open_card_order_modal', true);
                }
            });
        }
    }


}

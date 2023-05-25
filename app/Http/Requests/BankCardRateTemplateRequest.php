<?php

namespace App\Http\Requests;

use App\Enums\{Commissions, CommissionType, ComplianceLevel, Currency, RateTemplatesStatuses};
use App\Rules\NoEmojiRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankCardRateTemplateRequest extends FormRequest
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

        $numberRule = ['numeric', 'regex:/^\d{1,17}(\.\d{1,3})?$/'];

        $rules =  [
            "status" => ['bail', 'required', Rule::in(array_keys(RateTemplatesStatuses::STATUSES))],
            "bankCardOverviewType" => ['bail', 'required', 'string', new NoEmojiRule],
            "bankCardOverviewFee" => array_merge(['bail', 'required'], $numberRule),
            "bankCardTransactionsType" => ['bail', 'required', 'string', new NoEmojiRule],
            "bankCardTransactionsFee" => array_merge(['bail', 'required'], $numberRule),
            "bankCardFeesType" => ['bail', 'required', 'string', new NoEmojiRule],
            "bankCardFeesFee" => array_merge(['bail', 'required'], $numberRule),
        ];

        if($this->bank_card_rate_template_id) {
            $rules['bank_card_rate_template_id'] = ['bail', 'required', 'exists:bank_card_rate_templates,id'];
        } else {
            $rules["bankCardRateName"] = ['bail', 'required', 'string', 'unique:bank_card_rate_templates,name', new NoEmojiRule];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                if($this->bank_card_rate_template_id) {
                    $validator->errors()->add('bankCardRateErrorsUpdate', true);
                } else {
                    $validator->errors()->add('bankCardRateErrors', true);
                }
            }
        });
    }

}

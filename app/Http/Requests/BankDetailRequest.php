<?php

namespace App\Http\Requests;

use App\Enums\TemplateType;
use App\Rules\NoEmojiRule;

class BankDetailRequest extends BaseRequest
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
            'template_name' => ['required', new NoEmojiRule],
            'country' => 'required',
            'currency' => 'required',
            'type' => 'required',
            'iban' => !is_null($this->get('type')) && $this->get('type') == TemplateType::TEMPLATE_TYPE_SWIFT ? [new NoEmojiRule] : ['required', new NoEmojiRule],
            'swift' => ['required', new NoEmojiRule],
            'account_holder' => ['required', 'nullable', new NoEmojiRule],
            'account_number' => 'required|nullable|string',
            'bank_name' => ['required', new NoEmojiRule],
            'bank_address' => ['required', new NoEmojiRule],
            'correspondent_bank' => ['nullable', 'string', new NoEmojiRule],
            'correspondent_bank_swift' => ['nullable', 'string', new NoEmojiRule],
            'intermediary_bank' => ['nullable', 'string', new NoEmojiRule],
            'intermediary_bank_swift' => ['nullable', 'string', new NoEmojiRule],
        ];
    }

    public function messages()
    {
        return [
            'template_name.required' => t('provider_field_required'),
            'country.required' => t('provider_field_required'),
            'currency.required' => t('provider_field_required'),
            'type.required' => t('provider_field_required'),
            'iban.required' => t('provider_field_required'),
            'swift.required' => t('provider_field_required'),
            'account_holder.required' => t('provider_field_required'),
            'account_number.required' => t('provider_field_required'),
            'bank_name.required' => t('provider_field_required'),
            'bank_address.required' => t('provider_field_required'),
            'template_name.regex' => t('provider_field_regex'),
            'country.regex' => t('provider_field_regex'),
            'currency.regex' => t('provider_field_regex'),
            'iban.regex' => t('provider_field_regex'),
            'swift.regex' => t('provider_field_regex'),
            'account_holder.regex' => t('provider_field_regex'),
            'account_number.string' => t('provider_field_regex'),
            'bank_name.regex' => t('provider_field_regex'),
            'bank_address.regex' => t('provider_field_regex'),
        ];
    }

    public function withValidator($validator): void
    {
        if (!$this->wantsJson()) {
            $validator->after(function ($validator) {
                if ($validator->errors()->any()) {
                    if($this->get('type') == TemplateType::TEMPLATE_TYPE_SWIFT) {
                        $validator->errors()->add('bank_detail_type', $this->get('type'));
                    }
                }
            });
        }
    }
}

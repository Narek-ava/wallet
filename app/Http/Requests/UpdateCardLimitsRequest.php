<?php

namespace App\Http\Requests;


use App\Enums\TemplateType;
use App\Rules\CheckArrayElementsRule;
use App\Services\Wallester\Api;

class UpdateCardLimitsRequest extends BaseRequest
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

        return [
            "limits" => ['bail' ,'required', 'array', 'min:12',],
            "limits.*" => ['bail', 'required', 'numeric', 'gte:0',  new CheckArrayElementsRule($this->limits, $defaultLimitsArray,'max_card_', '_limit')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                $validator->errors()->add('show_limits_modal', true);
            }
        });
    }
}

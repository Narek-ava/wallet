<?php

namespace App\Http\Requests\Cabinet\API\v1;

use App\Enums\OperationType;
use Illuminate\Validation\Rule;

class OperationHistoryRequest extends \App\Http\Requests\BaseRequest
{
    public function rules()
    {
        $rules = [
            'transaction_type' => ['nullable', Rule::in(array_keys(OperationType::VALUES))],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d']
        ];

        if ($this->to) {
            $rules['from'][] = 'before:to';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'from.date_format' => t('error_from_date_format'),
            'to.date_format' => t('error_to_date_format')
        ];
    }

}

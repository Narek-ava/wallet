<?php

namespace App\Http\Requests\API\v1;

use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class FiatTopUpWireRequest extends BaseRequest
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
            'wallet_id' => ['required', 'string'],
            'wire_type' => ['required', Rule::in(OperationOperationType::API_WIRE_TYPES)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'provider_id' => ['required', 'string'],
        ];
    }
}

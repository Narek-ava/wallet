<?php

namespace App\Http\Requests;

use App\Enums\WallesterCardOrderPaymentMethods;
use App\Enums\WallesterCardTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WallesterPaymentMethodRequest extends BaseRequest
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
            'paymentMethod' => ['required', 'int', Rule::in(array_keys(WallesterCardOrderPaymentMethods::NAMES))],
            'type' => ['required', 'int', Rule::in(array_keys(WallesterCardTypes::NAMES))],
            'id' => ['required', 'exists:wallester_account_details,id'],
        ];
    }
}

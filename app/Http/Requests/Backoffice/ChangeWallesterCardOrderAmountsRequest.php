<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class ChangeWallesterCardOrderAmountsRequest extends FormRequest
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
            'plastic_card_order_amount' => ['bail', 'required', 'numeric', 'gte:0'],
            'virtual_card_order_amount' => ['bail', 'required', 'numeric', 'gte:0'],
        ];
    }
}

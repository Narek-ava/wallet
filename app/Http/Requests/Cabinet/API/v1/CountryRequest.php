<?php

namespace App\Http\Requests\Cabinet\API\v1;


use Illuminate\Validation\Rule;

class CountryRequest extends \App\Http\Requests\BaseRequest
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
            'banned' => ['bail', 'nullable', Rule::in(array_keys(\App\Models\Country::BANNED_NAMES))],
            'name' => ['nullable', 'string'],
            'code' => ['nullable', 'string'],
            'phone_code' => ['nullable', 'string'],
        ];
    }

    public function attributes()
    {
        return [
            'banned' => 'banned status'
        ];
    }
}

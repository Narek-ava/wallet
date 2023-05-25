<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class KYTProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
//        dd($this->all());
        $rules = [
            'name' => ['bail', 'required', 'unique:kyt_providers,name,' . $this->provider_id , "regex:/^[a-zA-Z].*([.'`0-9\p{Latin}]+[\ \-]?)+[a-zA-Z0-9 ]+$/"],
            'status' => "required",
            'api' => ['bail', "required", "string"],
            'api_account' => ['bail', "required", "string"],
        ];

        return $rules;
    }
}

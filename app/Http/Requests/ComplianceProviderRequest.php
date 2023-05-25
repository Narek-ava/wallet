<?php

namespace App\Http\Requests;

use App\Enums\ExchangeApiProviders;
use App\Services\ProviderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplianceProviderRequest extends FormRequest
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
        $rules = [
            'name' => ['bail', 'required', 'unique:compliance_providers,name,' . $this->provider_id , "regex:/^[a-zA-Z].*([.'`0-9\p{Latin}]+[\ \-]?)+[a-zA-Z0-9 ]+$/"],
            'status' => "required",
            'api' => ['bail', "required", "string"],
            'api_account' => ['bail', "required", "string"],
        ];

        return $rules;
    }
}

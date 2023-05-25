<?php

namespace App\Http\Requests;

use App\Models\ApiClient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiClientsRequest extends FormRequest
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
            'project_id' => ['required', 'string', 'exists:projects,id'],
            'name' => ['required', 'string', 'unique:api_clients,name,' . $this->route('api_client') ],
            'key' => ['required', 'string'],
            'apiToken' => ['required', 'string'],
            'status' => ['required', 'int', Rule::in(array_keys(ApiClient::STATUS_NAMES))],
            'accessTokenExpiresTime' => ['required', 'int', 'min:1', 'max:24'],
            'refreshTokenExpiresTime' => ['required', 'int', 'min:1', 'max:30'],
        ];
    }


}

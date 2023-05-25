<?php

namespace App\Http\Requests\Cabinet;

use App\Http\Requests\Cabinet\API\v1\BaseRequest;

class DeleteAccountRequest extends BaseRequest
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
            'account_id' => 'required|string|exists:accounts,id'
        ];
    }

    public function messages()
    {
        return [
            'account_id.exists' => t('account_id_exists'),
        ];
    }

}

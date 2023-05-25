<?php

namespace App\Http\Requests\Backoffice;

use App\Http\Requests\BaseRequest;

class ProjectCardIssuingSettingsRequest extends BaseRequest
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
            'projectId' => ['required', 'string', 'exists:projects,id'],
            'issuer' => ['required', 'string'],
            'audience' => ['required', 'string'],
            'appUrl' => ['required', 'string'],
            'appSite' => ['required', 'string']
        ];
    }
}

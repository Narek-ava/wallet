<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'unique:roles,name,' . $this->route('role') ],
            'permissions' => ['bail', 'required', 'array', 'min:1'],
            'permissions.*' => ['bail', 'required', 'string', 'exists:permissions,name'],
        ];
    }

    public function attributes()
    {
        return [
            'permissions.*' => 'permission'
        ];
    }
}

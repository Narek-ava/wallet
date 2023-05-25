<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;

class BUserRequest extends FormRequest
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

        $bUserId = $this->route('b_user')->id ?? null;

        return [
            'first_name' => ['bail', 'nullable', 'string', 'max:255','regex:/^[a-zA-Z ]+$/u'],
            'last_name' => ['bail', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z ]+$/u'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', 'unique:b_users,email,' .  $bUserId],
            'phone' => ['bail', 'nullable', 'string', 'min:5', 'max:15','regex:/^([0-9]*)$/'],
        ];
    }
}

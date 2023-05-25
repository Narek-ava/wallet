<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{

    public function wantsJson()
    {
        return strpos($this->url(), '/api/') !== false;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $errors = $validator->errors();
            throw new HttpResponseException(response()->json([
                'errors' => array_map(function ($error) {
                    return $error[0] ?? $error;
                }, $errors->getMessages())
            ], 422));
        } else {
            parent::failedValidation($validator);
        }
    }
}
